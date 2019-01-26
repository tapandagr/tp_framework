{if isset($category) and $category->id > 0}
    <div class="col-lg-2">
        <div class="up tp-category-view panel" data-category="{$parent->id}">
            <div class="image">
                <i class="fas fa-ellipsis-h fa-10x"></i>
            </div>
            <div class="meta-title">{$parent->meta_title}</div>
        </div>
    </div>
{/if}
{assign var=x value=0}
{foreach $children as $c}
	<div class="col-lg-2">
		<div class="category view panel" data-category="{$c.id_tp_framework_category}">
			<input type="checkbox" name="check-category[{$x}]" value="{$c.id_tp_framework_category}" class="check-category">
			<div class="img">
				<i class="fas fa-folder-open fa-10x"></i>
				<div class="toolbar">
					<div class="medium_edit">
						<a href="" title="{l s='Edit'}" class="open_edit">
							<i class="fas fa-pencil-alt"></i>
						</a>
					</div>
				</div>
			</div>
			<div class="meta-title">{$c.meta_title}</div>
		</div>
	</div>
	{assign var=x value=$x+1}
{/foreach}
{if $files != ''}
	{assign var=x value=0}
	{foreach $files as $f}
		<div class="col-lg-2">
			<div class="media relation_update panel">
				<input type="checkbox" name="check_file[{$x}]" value="{$f.id_tp_framework_medium}">
				<div class="img">
					<img src="/modules/tp_framework/uploads/img{$f.location}{$f.cat_link}/{$f.sub}/{$f.link}.png">
				</div>
				<div class="meta_title">
					{$f.link}
				</div>
			</div>
		</div>
		{assign var=x value=$x+1}
	{/foreach}
{/if}
