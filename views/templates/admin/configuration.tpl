{**
 * Cornelius - Core PrestaShop module
 *
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/2-basic-license
 *}
<div class="panel">
    <div class="panel-heading">{l s='Cron files' d='Modules.Tvcore.Admin'}</div>
    <p>
        <strong>{l s='Add index:' d='Modules.Tvcore.Admin'}</strong>
        <a href="{$add_index_cron|escape:'htmlall':'UTF-8'}" target="_blank">
            {$add_index_cron|escape:'htmlall':'UTF-8'}
        </a>
    </p>
    {foreach $cron_links as $cron_link}
        <p>
            <strong>{$cron_link.label|escape:'htmlall':'UTF-8'}</strong>
            <a href="{$cron_link.href|escape:'htmlall':'UTF-8'}" target="_blank">
                {$cron_link.href|escape:'htmlall':'UTF-8'}
            </a>
        </p>
    {/foreach}
</div>
