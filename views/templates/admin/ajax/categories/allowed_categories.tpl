{if isset($allowed_categories)}
	{foreach $allowed_categories as $c}
		<div class="parent" data-id="{$c.id_tp_framework_category}" data-name="{$c.meta_title}">{'-- '|str_repeat:$c.level}{$c.meta_title}</div>
	{/foreach}
{else}
	<div class="cat_value">{l s='Επιλέξτε δεδομένα προς μεταφορά'}</div>
{/if}
