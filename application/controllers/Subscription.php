<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Subscription extends ClientsController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('subscriptions_model');
        $this->load->library('stripe_subscriptions');
        $this->load->library('stripe_core');
    }

    public function index($hash = '')
    {
        $subscription = $this->subscriptions_model->get_by_hash($hash);

        if (! $hash || ! $subscription) {
            show_404();
        }

        $language               = load_client_language($subscription->clientid);
        $data['locale']         = get_locale_key($language);
        $data['publishableKey'] = $this->stripe_subscriptions->get_publishable_key();
        $plan                   = $this->stripe_subscriptions->get_plan($subscription->stripe_plan_id);

        check_stripe_subscription_environment($subscription);

        if (! empty($subscription->stripe_subscription_id) && ! empty($data['publishableKey'])) {
            $data['stripeSubscription'] = $this->stripe_subscriptions->get_subscription([
                'id'     => $subscription->stripe_subscription_id,
                'expand' => ['latest_invoice'],
            ]);

            if ($this->input->get('complete')) {
                redirect($data['stripeSubscription']->latest_invoice->hosted_invoice_url);
            }
        }

        $upcomingInvoice                    = new stdClass();
        $upcomingInvoice->default_tax_rates = null;
        $upcomingInvoice->total             = $plan->amount * $subscription->quantity;
        $upcomingInvoice->subtotal          = $upcomingInvoice->total;
        $total                              = $upcomingInvoice->total;

        if (! empty($subscription->tax_percent) || ! empty($subscription->tax_percent_2)) {
            $totalTax                           = 0;
            $upcomingInvoice->default_tax_rates = [];
            if (! empty($subscription->tax_percent)) {
                $tax1                                 = new stdClass();
                $tax1->percentage                     = $subscription->tax_percent;
                $tax1->display_name                   = $subscription->tax_name;
                $upcomingInvoice->default_tax_rates[] = $tax1;
                $totalTax += $upcomingInvoice->total * ($subscription->tax_percent / 100);
            }

            if (! empty($subscription->tax_percent_2)) {
                $tax2                                 = new stdClass();
                $tax2->percentage                     = $subscription->tax_percent_2;
                $tax2->display_name                   = $subscription->tax_name_2;
                $upcomingInvoice->default_tax_rates[] = $tax2;
                $totalTax += $upcomingInvoice->total * ($subscription->tax_percent_2 / 100);
            }

            $upcomingInvoice->total += $totalTax;
        }

        $data['total'] = $upcomingInvoice->total;
        $product       = $this->stripe_subscriptions->get_product($plan->product);

        $upcomingInvoice->lines       = new stdClass();
        $upcomingInvoice->lines->data = [];

        $upcomingInvoice->lines->data[] = [
            'description' => $this->lineProductDescription($product, $plan, $subscription->currency_name),
            'amount'      => $plan->amount * $subscription->quantity,
            'quantity'    => $subscription->quantity,
        ];

        $this->disableNavigation();
        $this->disableSubMenu();
        $data['child_invoices'] = $this->subscriptions_model->get_child_invoices($subscription->id);
        $data['invoice']        = subscription_invoice_preview_data($subscription, $upcomingInvoice);
        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');
        $data['plan']         = $plan;
        $data['subscription'] = $subscription;
        $data['title']        = $subscription->name;
        $data['hash']         = $hash;
        $data['bodyclass']    = 'subscriptionhtml';
        $this->data($data);
        $this->view('subscriptionhtml');
        $this->layout();
    }

    public function subscribe($subscription_hash)
    {
        $subscription = $this->subscriptions_model->get_by_hash($subscription_hash);

        if (! $subscription) {
            show_404();
        }

        $stripe_customer_id = $subscription->stripe_customer_id;
        $cancelUrl          = site_url('subscription/' . $subscription_hash);

        $sessionData = [
            'payment_method_types' => ['card'],
            'mode'                 => 'subscription',
            'success_url'          => site_url('subscription/complete_setup/' . $subscription->hash . '?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url'           => $cancelUrl,
            'subscription_data'    => $this->create_subscription_data($subscription),
            'line_items'           => [
                [
                    'price'    => $subscription->stripe_plan_id,
                    'quantity' => $subscription->quantity ?: 1,
                ],
            ],
            'custom_text' => [],
        ];

        if (! empty($subscription->terms)) {
            $sessionData['consent_collection']['terms_of_service']                = 'required';
            $sessionData['custom_text']['terms_of_service_acceptance']['message'] = clear_textarea_breaks($subscription->terms);
        }

        if (! empty($subscription->description)) {
            $sessionData['custom_text']['submit']['message'] = clear_textarea_breaks($subscription->description);
        }

        if ($stripe_customer_id) {
            $sessionData['customer'] = $stripe_customer_id;
        } elseif (is_client_logged_in()) {
            $contact = $this->clients_model->get_contact(get_contact_user_id());
            if ($contact->email) {
                $sessionData['customer_email'] = $contact->email;
            }
        }

        $sessionData = hooks()->apply_filters('stripe_subscription_session_data', $sessionData, $subscription_hash);

        try {
            $session = $this->stripe_core->create_session($sessionData);
        } catch (Exception $e) {
            set_alert('warning', $e->getMessage());

            redirect($cancelUrl);
        }

        redirect_to_stripe_checkout($session->id);
    }

    /**
     * After collection payments for future subscriptions
     *
     * @param mixed $hash
     *
     * @return mixed
     */
    public function complete_setup($hash)
    {
        $subscription = $this->subscriptions_model->get_by_hash($hash);

        if (! $subscription) {
            show_404();
        }

        try {
            $session = $this->stripe_core->retrieve_session([
                'id'     => $this->input->get('session_id'),
                'expand' => ['subscription'],
            ]);

            $client = $this->clients_model->get($subscription->clientid);

            $customerPayload = [
                'email'       => $session->customer_details->email,
                'name'        => $session->customer_details->name,
                'description' => $subscription->company,
                // https://stripe.com/docs/india-accept-international-payments
                'address' => [
                    'line1'       => $client->billing_street,
                    'postal_code' => $client->billing_zip,
                    'city'        => $client->billing_zip,
                    'state'       => $client->billing_state,
                    'country'     => get_country($client->billing_country)->iso2 ?? null,
                ],
            ];

            // Update the existing customer with the new provided email and name
            // this can happen if customer previously paid only invoice and it was saved in database
            // but without payment method, now becase above client_reference_id is passed
            // so we can determine here the customer
            $customer = $this->stripe_core->update_customer($session->customer, $customerPayload);

            $pm = Stripe\PaymentMethod::retrieve($session->subscription->default_payment_method);
            $pm->attach(['customer' => $customer->id]);

            $this->stripe_core->update_customer($customer->id, [
                'invoice_settings' => [
                    'default_payment_method' => $session->subscription->default_payment_method,
                ],
            ]);

            // In case the webhook is slower, update the stripe_subscription_id so the user won't see the subscribe button again
            $this->subscriptions_model->update($subscription->id, ['stripe_subscription_id' => $session->subscription->id]);
            set_alert('success', _l('customer_successfully_subscribed_to_subscription', $subscription->name));
        } catch (Exception $e) {
            set_alert('warning', $e->getMessage());
        }

        redirect(site_url('subscription/' . $hash));
    }

    protected function create_subscription_data($subscription)
    {
        $params                = [];
        $params['description'] = $subscription->name;

        if (! empty($subscription->stripe_tax_id) || ! empty($subscription->stripe_tax_id_2)) {
            $params['default_tax_rates'] = [];
            if (! empty($subscription->stripe_tax_id)) {
                $params['default_tax_rates'][] = $subscription->stripe_tax_id;
            }

            if (! empty($subscription->stripe_tax_id_2)) {
                $params['default_tax_rates'][] = $subscription->stripe_tax_id_2;
            }
        }

        $params['metadata'] = [
            'pcrm-subscription-hash' => $subscription->hash,
            // Indicated the the customer was on session,
            // see requires action event
            'customer-on-session' => true,
        ];

        if (! empty($subscription->date)) {
            if ($subscription->date > date('Y-m-d')) {
                // is future
                $params['billing_cycle_anchor'] = strtotime($subscription->date);
                // https://stripe.com/docs/billing/subscriptions/billing-cycle#new-subscriptions
                $params['proration_behavior'] = 'none';
            }
        }

        return $params;
    }

    /**
     * After stripe checkout succcess
     * Used only to display success message to the customer
     *
     * @param string $invoice_id   The invoice id the payment is made to
     * @param strgin $invoice_hash invoice hash
     * @param mixed  $hash
     *
     * @return mixed
     */
    public function success($hash)
    {
        $subscription = $this->subscriptions_model->get_by_hash($hash);

        set_alert('success', _l('customer_successfully_subscribed_to_subscription', $subscription->name));

        send_email_customer_subscribed_to_subscription_to_staff($subscription);

        redirect(site_url('subscription/' . $hash));
    }

    protected function lineProductDescription($product, $plan, $currency)
    {
        $intervals = ['day', 'week', 'month', 'year'];
        $interval  = $plan->interval;

        foreach ($intervals as $stripeInterval) {
            if ($plan->interval === $stripeInterval && $plan->interval_count === 1) {
                $interval = _l($stripeInterval);
            } elseif ($plan->interval === $stripeInterval && $plan->interval_count > 1) {
                $interval = _l('frequency_every', $plan->interval_count . ' ' . _l($stripeInterval . 's'));
            }
        }

        $productName = (! empty($plan->nickname) ? $plan->nickname : $product->name);

        return $productName . ' (' . app_format_money(strcasecmp($plan->currency, 'JPY') == 0 ?
                $plan->amount :
                $plan->amount / 100, strtoupper($currency)) . ' / ' . $interval . ')';
    }
}
