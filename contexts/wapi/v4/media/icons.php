<?php
use model\media\Icons;
use utils\ParamUtil;
use model\media\IconSets;

function _exec()
{
    $icons = new Icons();
    $params = WAPI::GetParams();
    
    $action = ParamUtil::RequiresOne($params, 'action');
    $target = ParamUtil::RequiresOne($params, 'target');
    
    switch ($action) {
        case WAPI::ACTION_LIST:
            switch ($target) {
                case 'icon_sets':
                    $iconSetData = IconSets::ListIconSets();
                    return WAPI::SendSimpleResults(array_keys($iconSetData));
                case 'icons':
                    $iconSet = ParamUtil::Get($params, 'icon_set');
                    if ($iconSet) {
                        $iconData = IconSets::ListIcons($iconSet);
                        return WAPI::SendSimpleResults($iconData);
                    }
                    $iconData = $icons->ListIcons($params);
                    return WAPI::SendSimpleResults($iconData);
                
                case 'categories':
                    return WAPI::SendSimpleResults($icons->ListCategories());
                case 'sizes':
                    return WAPI::SendSimpleResults($icons->ListSizes());
            }
        case WAPI::ACTION_GET:
            switch ($target) {
                case 'icon':
                    $icons->GetIcon($params, true);
                    return;
            }
        case 'catalog':
            $category = ParamUtil::RequiresOne($params, 'category');
            $size = ParamUtil::Get($params, 'size', '32px');
            $iconData = $icons->ListIcons($params);
            $lineSize = (intval(str_replace('px', '', $size)) + 10) . 'px';
            echo <<<HTML
<html>
<head>
<title>Icon Catalog: $category</title>
<style>
table {
		border-style: solid;
		border-color:#000;
		border-weight: 1px;
		}
th {
			vertical-align:left;	
			line-height: 24px;
			
			max-height: 10px;	
		}
tr:nth-child(even) {
		background:#ccc;
		border-st
		}
tr {
	line-height: $lineSize;
	vertical-align:middle;		
}
tr td:nth-child(2) {
		padding-left: 10px;
}
</style>
<body>
<table><tbody>
HTML;
            echo "<tr><th>Icon</th><th>Icon Name</th><th>Icon</th><th>Icon Name</th><th>Icon</th><th>Icon Name</th><th>Icon</th><th>Icon Name</th><th>Icon</th><th>Icon Name</th></tr>";
            $i = 0;
            foreach ($iconData as $icon) {
                $row = floor($i / 5);
                if ($i - $row * 5 == 0)
                    echo "<tr>";
                echo ("<td><img src='" . $icons->GetIconURL($category, $icon, $size) . "'></img></td><td>$i:$icon</td>");
                if (($i - $row * 5) == 4) {
                    echo "</tr>";
                    $row ++;
                }
                $i ++;
            }
            echo "</tbody></body></html>";
    }
    
}
?>