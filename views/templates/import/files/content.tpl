<div class="tp-content">
	<div class="loader hidden">
        <div class="spinner">
            <div class="cube1"></div>
            <div class="cube2"></div>
        </div>
		<div class="message">
			{l s='Παρακαλούμε περιμένετε. Φορτώνει'}...
		</div>
	</div>
	{if isset($errors) and !empty($errors)}
		<div class="alert alert-danger" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><i class="material-icons">close</i></span>
			</button>
			<div class="alert-text">
				{foreach $errors as $e}
					<p>{$e}</p>
				{/foreach}
			</div>
		</div>
	{/if}
	<div class="tp-ajax-result col-lg-12 col-xs-12"></div>

	<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  		<div class="modal-dialog modal-dialog-centered" role="document">
    		<div class="modal-content">
				<form action="{$links->admin->categories->add}" method="post" data-pure-link="{$links->admin->categories->url}" data-refresh="1">

      				<div class="modal-header">
        				<h5 class="modal-title" id="exampleModalLongTitle">{l s='Προσθήκη κατηγορίας αρχείων'}</h5>
        				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
          					<i class="fas fa-times"></i>
        				</button>
      				</div>
      				<div class="modal-body">
			            {foreach $fields->category as $f}
			                {if $f.name == 'categories'}
			                    <div class="replace-select field col-lg-6">
			                        <div class="container">
			                            <label class="inactive" id="meta_title" for="display">
			                                {l s='Επιλέξτε κατηγορία'}
			                            </label>
			                            <i class="fas fa-chevron-down down"></i>
			                            <i class="fas fa-chevron-up up"></i>
			                            <div class="refresh-categories menu">
			                                {foreach $tree as $c}
			                                    {if $c.parent_id < 1}
			                                        <div class="parent" data-id="{$c.id_tp_framework_category}" data-name="{$c.meta_title}">{$c.meta_title}</div>
			                                    {/if}
			                                    {if isset($c.descendants)}
			                                        {foreach $c.descendants as $d}
			                                            <div class="parent" data-id="{$d.id_tp_framework_category}" data-name="{$d.meta_title}">{'-- '|str_repeat:$d.level}{$d.meta_title}</div>
			                                        {/foreach}
			                                    {/if}
			                                {/foreach}
			                            </div>
			                            <input type="hidden" name="parent_id" class="parent_hidden">
			                        </div>
			                    </div>
			                {elseif $f.name == 'meta_title'}
			                    <div class="field {$f.name} col-lg-6">
			                        {foreach $languages as $l}
			                            <input type="text" name="{$f.name}[]" class="value lang-field{if $current_language->iso_code != $l.iso_code} hidden{/if} {$l.iso_code}">
			                        {/foreach}
			                        <label class="move_right">{l s='Meta τίτλος'}</label>
			                    </div>
			                {elseif $f.name == 'link_rewrite'}
			                    <div class="field {$f.name} col-lg-{if $column_remainder == 6}6{else}12{/if}">
			                        <input type="text" name="{$f.name}" class="value">
			                        <label class="move_right">{l s='Σύνδεσμος'}</label>
			                    </div>
			                {/if}
			            {/foreach}
      				</div>
      				<div class="modal-footer">
						<input type="hidden" value="{$links->admin->categories->token}" name="token">
			            <div class="languages replace-select">
							<div class="container">
								<label class="current inactive">
									{$current_language->iso_code}
									{if count($languages) > 1}
										<i class="fas fa-chevron-down"></i>
										<i class="fas fa-chevron-up hidden"></i>
									{/if}
								</label>
								<div class="menu">
									{foreach $languages as $l}
										<div class="value tp-change-lang" data-value="{$l.iso_code}">{$l.iso_code}</div>
									{/foreach}
								</div>
							</div>
						</div>
						<button type="submit" class="tp-ajax-submit btn btn-primary" data-action="Add">{l s='Προσθήκη κατηγορίας'}</button>
      				</div>
				</form>
    		</div>
  		</div>
	</div>
	<div class="main">
		<form method="post" action="" data-pure-link="{$links->admin->categories->url}" class="update-files-form">
			<div class="files-browser" data-current-category="{if isset($category)}{$category->id}{else}0{/if}">
				{if isset($category) and $category->id > 0}
					<div class="col-lg-2">
						<div class="up panel">
							<div class="image">
								<i class="fas fa-ellipsis-h fa-10x"></i>
							</div>
							<div class="meta-title">
								<a class="view-category" data-category="{$parent->id}">
									{$parent->meta_title}
								</a>
							</div>
						</div>
					</div>
				{/if}
				{assign var=x value=0}
				{foreach $category->children as $c}
					<div class="col-lg-2">
						<div class="category check-file panel">
							<input type="checkbox" name="check-category[{$x}]" value="{$c.id_tp_framework_category}" class="check-category">
							<div class="image">
								<i class="fas fa-folder-open fa-4x"></i>
							</div>
							<div class="meta-title">
								<a class="view-category" data-category="{$c.id_tp_framework_category}">
									{$c.meta_title}
								</a>
							</div>
						</div>
					</div>
					{assign var=x value=$x+1}
				{/foreach}
				<input type="hidden" name="move-to-category" class="move-to-category">
			</div>
			<div class="footer toolbar">
				<div class="panel">
					<div class="replace-select field col-lg-4 col-sm-6">
						<div class="container">
							<label for="view-cateogory">
								{l s='Επιλέξτε κατηγορία'}
							</label>
							<i class="fas fa-chevron-down down"></i>
							<i class="fas fa-chevron-up up"></i>
							<div class="view-category menu">
								{foreach $tree as $c}
									<div data-value="{$c.id_tp_framework_category}">
										{$c.meta_title}
									</div>
								{/foreach}
							</div>
						</div>
					</div>
					<div class="replace-select field col-lg-4 col-sm-6">
						<div class="container">
							<label for="move-to-cateogory">
								{l s='Μετακίνηση σε κατηγορία'}
							</label>
							<i class="fas fa-chevron-down down"></i>
							<i class="fas fa-chevron-up up"></i>
							<div class="move-to-category menu">
								{foreach $tree as $c}
									<div data-value="{$c.id_tp_framework_category}">
										{$c.meta_title}
									</div>
								{/foreach}
							</div>
						</div>
					</div>
					<div class="col-lg-4 col-sm-6">
						<div class="update-selected-files button">
							<a href="#" title="{l s='Ενημέρωση'}">
								<i class="fas fa-cogs"></i>
								<label for="">{l s='Ενημέρωση'}</label>
							</a>
						</div>
						<div class="delete-selected-files button">
							<a href="#" title="{l s='Διαγραφή'}">
								<i class="fas fa-trash"></i>
								<label for="">{l s='Διαγραφή'}</label>
							</a>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
