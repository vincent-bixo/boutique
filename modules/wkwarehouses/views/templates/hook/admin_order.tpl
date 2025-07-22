{*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if $orderLocations|@count}
<script type="text/javascript">
	var array_locations = '{$orderLocations|json_encode}';
	array_locations = array_locations.replace(/&quot;/ig,'"');
	var orderLocations = JSON.parse(array_locations);
</script>
{/if}
