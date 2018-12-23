{if isset($allowed_categories)}
	{foreach $allowed_categories as $c}
		{if $c.level < 1}
			<div class="parent" data-id="{$c.id_tp_framework_category}" data-name="{$c.meta_title}">{$c.meta_title}</div>
		{/if}
		{if isset($c.descendants)}
			{foreach $c.descendants as $cd}
				<div class="parent" data-id="{$cd.id_tp_framework_category}" data-name="{$cd.meta_title}">{$c.meta_title}</div>
			{/foreach}
		{/if}
	{/foreach}
{else}
	<div class="cat_value">{l s='Επιλέξτε δεδομένα προς μεταφορά'}</div>
{/if}
