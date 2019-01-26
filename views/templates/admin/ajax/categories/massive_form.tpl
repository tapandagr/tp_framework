<div class="col-lg-12">
	{assign var=x value=0}
	{foreach $category->entities as $c}
		<div class="panel">
			<input type="hidden" name="data[{$x}][id_tp_framework_category]" value="{$c.id_tp_framework_category}">
			{foreach $category->fields as $f}
                {if $f.type == 'select'}
					<div class="field col-lg-{$f.width}">
						<label class="replace_select inactive" id="meta_title" for="display">Select category</label>
						<div class="menu">
							{foreach $c['allowed_categories'] as $ac}
								<div class="parent id value" data-id="{$ac.id_tp_framework_category}">{$ac.meta_title}</div>
							{/foreach}
						</div>
						<input type="hidden" name="data[{$x}][parent_id]" class="parent_hidden" value="{$c['allowed_categories'][0]['id_tp_framework_category']}">
					</div>
				{elseif $f.name == 'meta_title'}
					<div class="floating field {$f.name} col-lg-{$f.width}">
						{assign var=y value=0}
						{foreach $language->entities as $l}
							<input
								type="text"
								name="data[{$x}][meta_title][{$l.id_lang}]"
								class="value lang-field{if $language->current->iso_code != $l.iso_code} hidden{/if} {$l.iso_code}"
								value="{$c['meta_title'][$l.id_lang]}"
							>
							{assign var=y value=$y+1}
						{/foreach}
						<label>{l s='Meta title'}</label>
					</div>
				{elseif $f.type == 'text'}
					<div class="filled field {$f.name} col-lg-{$f.width}">
						<input type="text" name="data[{$x}][{$f.name}]" class="value" value="{$c[$f['name']]}">
						<label>{$f.name}</label>
					</div>
				{/if}
			{/foreach}
		</div>
		{assign var=x value=$x+1}
	{/foreach}
	<div class="panel">
        <div class="replace-select field col-lg-3 col-xs-12">
			<div class="container">
                <label>{$language->current->iso_code}</label>
				<i class="fas fa-chevron-down down"></i>
				<i class="fas fa-chevron-up up"></i>
				<div class="view-languages menu">
                    {foreach $language->entities as $l}
        				<div class="tp-change-lang" data-value="{$l.iso_code}">{$l.iso_code}</div>
        			{/foreach}
                </div>
            </div>
        </div>
        <div class="submit col-lg-9 col-xs-12">
			<button type="submit" class="tp-ajax-submit" data-action="MassiveUpdate">{l s='Αποθήκευση ρυθμίσεων'}</button>
		</div>
    </div>
</div>
