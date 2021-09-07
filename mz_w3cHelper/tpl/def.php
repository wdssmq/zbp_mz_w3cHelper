{foreach $rltMaps as $type => $item}
<h4>{$type}</h4>
{if is_array($item)}
{foreach $item as $link}
{template:plugin_mz_w3cHelper_link}
{/foreach}
{else}
{php}
$link = $item;
{/php}
{template:plugin_mz_w3cHelper_link}
{/if}
{/foreach}
