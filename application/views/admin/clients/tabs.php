<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $tabs = filter_client_visible_tabs($customer_tabs, $client->userid); ?>

<select class="tw-block md:tw-hidden form-control" onchange="redirectToTab(this)">
  <?php foreach ($tabs as $key => $tab) { ?>
  <?php $current = (! $this->input->get('group') && $key === 'profile') || $this->input->get('group') === $key; ?>
  <option <?= $current ? 'selected' : ''; ?>
    data-key="<?= $key; ?>">
    <?= e($tab['name']); ?>
    <?php if (isset($tab['badge'], $tab['badge']['value']) && ! empty($tab['badge'])) { ?>
    (<?= e($tab['badge']['value']) ?>)
    <?php } ?>
  </option>
  <?php } ?>
</select>

<nav class="customer-tabs tw-hidden tw-flex-1 tw-flex-col md:tw-flex" aria-label="Sidebar">
  <ul role="list" class="tw-space-y-0.5">
    <?php foreach ($tabs as $key => $tab) { ?>
    <?php $current = (! $this->input->get('group') && $key === 'profile') || $this->input->get('group') === $key; ?>
    <li class="customer_tab_<?= e($key); ?>">
      <a href="<?= admin_url('clients/client/' . $client->userid . '?group=' . $key); ?>"
        data-group="<?= e($key); ?>"
        class="tw-group tw-flex tw-items-center tw-gap-x-3 tw-rounded-md tw-p-2 tw-font-medium <?= $current ? 'tw-bg-neutral-50 tw-text-primary-600' : 'tw-text-neutral-800 hover:tw-bg-neutral-50 hover:tw-text-primary-600'; ?>">

        <?php if (! empty($tab['icon'])) { ?>
        <i class="<?= e($tab['icon']); ?> fa-lg fa-fw tw-shrink-0 <?= $current ? 'tw-text-primary-600' : 'tw-text-neutral-400 group-hover:tw-text-primary-600'; ?>"
          aria-hidden="true"></i>
        <?php } ?>

        <span><?= e($tab['name']); ?></span>

        <?php if (isset($tab['badge'], $tab['badge']['value']) && ! empty($tab['badge'])) {?>
        <span
          class="badge tw-ml-auto
            <?= isset($tab['badge']['type']) && $tab['badge']['type'] != '' ? "bg-{$tab['badge']['type']}" : 'bg-info' ?>"
          <?= (isset($tab['badge']['type']) && $tab['badge']['type'] == '')
                      || isset($tab['badge']['color']) ? "style='background-color: {$tab['badge']['color']}'" : '' ?>>
          <?= e($tab['badge']['value']) ?>
        </span>
        <?php } ?>
      </a>
    </li>
    <?php } ?>
  </ul>
</nav>


<script>
  function redirectToTab(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const key = selectedOption.getAttribute('data-key');
    if (key) {
      const clientId = <?= json_encode($client->userid); ?> ;
      window.location.href =
        `<?= admin_url('clients/client/'); ?>${clientId}?group=${key}`;
    }
  }
</script>