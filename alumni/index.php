<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
/**
 * Alumni module for Xoops
 *
 * @copyright       XOOPS Project http://xoops.org/
 * @license         GPL 2.0 or later
 * @package         alumni
 * @since           2.6.x
 * @author          John Mordo (jlm69)
 */

use Xoops\Core\Request;

include __DIR__ . '/header.php';

$moduleDirName = basename(__DIR__);
$main_lang = '_MA_' . strtoupper($moduleDirName);
$myts      = MyTextSanitizer::getInstance();
$xoops     = Xoops::getInstance();
$module_id = $xoops->module->getVar('mid');

if (is_object($xoops->user)) {
    $groups = $xoops->user->getGroups();
} else {
    $groups = '3';
}
//$gperm_handler = $xoops->getHandler('groupperm');
if (isset($_POST['item_id'])) {
    $perm_itemid = (int)($_POST['item_id']);
} else {
    $perm_itemid = 0;
}
//If no access
if (!$gpermHandler->checkRight('' . $moduleDirName . '_view', $perm_itemid, $groups, $module_id)) {
    $xoops->redirect(XOOPS_URL . '/index.php', 3, XoopsLocale::E_NO_ACCESS_PERMISSION);
    exit();
}
if (!$gpermHandler->checkRight('' . $moduleDirName . '_premium', $perm_itemid, $groups, $module_id)) {
    $prem_perm = '0';
} else {
    $prem_perm = '1';
}

	$alumni = Alumni::getInstance();

$gpermHandler = $xoops->getHandlerGroupPermission();

//        $alumni_user = $gperm_handler->getItemIds('alumni_view', $xoops->user->getGroups(), $module_id);
//        $alumni_premium = $gperm_handler->getItemIds('alumni_submit', $xoops->user->getGroups(), $module_id);

//	if (!$alumni_user) {
//	$xoops->redirect(XOOPS_URL . "/user.php", 3, _NOPERM);
//	}
$totalCategories = $alumni->getCategoryHandler()->getCategoriesCount(0);

// if there ain't no category to display, let's get out of here
if ($totalCategories == 0) {
    $xoops->redirect(\XoopsBaseConfig::get('url'), 12, _NOPERM);
}

$xoops->header('module:alumni/alumni_index.tpl');
Xoops::getInstance()->header();
$xoops->tpl()->assign('xmid', $xoopsModule->getVar('mid'));
$xoops->tpl()->assign('add_from', AlumniLocale::ALUMNI_LISTINGS . ' ' . $xoopsConfig['sitename']);
$xoops->tpl()->assign('add_from_sitename', $xoopsConfig['sitename']);
$xoops->tpl()->assign('add_from_title', AlumniLocale::ALUMNI_LISTINGS);
$xoops->tpl()->assign('class_of', AlumniLocale::CLASSOF);
$xoops->tpl()->assign('front_intro', AlumniLocale::FINTRO);

if ($xoops->getModuleConfig('' . $moduleDirName . '_offer_search') == '1') {

    $xoops->tpl()->assign('offer_search', true);
    $xoops->tpl()->assign('search_listings', AlumniLocale::SEARCH_LISTINGS);
    $xoops->tpl()->assign('match', AlumniLocale::MATCH);
    $xoops->tpl()->assign('all_words', AlumniLocale::ALL_WORDS);
    $xoops->tpl()->assign('any_words', AlumniLocale::ANY_WORDS);
    $xoops->tpl()->assign('exact_match', AlumniLocale::EXACT_MATCH);
    $xoops->tpl()->assign('byyear', AlumniLocale::BYYEAR);
    $xoops->tpl()->assign('bycategory', AlumniLocale::BYCATEGORY);
    $xoops->tpl()->assign('keywords', XoopsLocale::KEYWORDS);
    $xoops->tpl()->assign('search', XoopsLocale::SEARCH);

    $categoriesHandler = $xoops->getModuleHandler('category', 'alumni');

    $alumni    = Alumni::getInstance();
    $helper          = $xoops->getModuleHelper('alumni');
    $module_id       = $helper->getModule()->getVar('mid');
    $groups          = $xoops->isUser() ? $xoops->user->getGroups() : '3';
    $alumni_ids      = $xoops->getHandlerGroupPermission()->getItemIds('alumni_view', $groups, $module_id);
    $cat_criteria = new CriteriaCompo();
    $cat_criteria->add(new Criteria('cid', '(' . implode(', ', $alumni_ids) . ')', 'IN'));
    $cat_criteria->setOrder('' . $xoops->getModuleConfig('' . $moduleDirName . '_csortorder') . '');
    $numcat       = $categoriesHandler->getCount($cat_criteria);
    $category_arr = $categoriesHandler->getall($cat_criteria);

    foreach (array_keys($category_arr) as $i) {
        $cid      = $category_arr[$i]->getVar('cid');
        $pid      = $category_arr[$i]->getVar('pid');
        $title    = $category_arr[$i]->getVar('title', 'e');
        $img      = $category_arr[$i]->getVar('img');
        $order    = $category_arr[$i]->getVar('ordre');
        $affprice = $category_arr[$i]->getVar('affprice');
        $title    = $myts->htmlSpecialChars($title);
        $xoops->tpl()->assign('title', $title);
    }

    include_once(XOOPS_ROOT_PATH . "/modules/{$moduleDirName}/class/alumni_tree.php");
    $cattree = new AlumniObjectTree($category_arr, 'cid', 'pid');

    $categories      = $alumni->getCategoryHandler()->getCategoriesForSearch();
    $by_cat          = Request::getInt('by_cat');
    $select_category = "<select name=\"by_cat\">";
    $select_category .= "<option value=\"all\"";
    if (empty($by_cat) || count($by_cat) == 0) {
        $select_category .= "selected=\"selected\"";
    }
    $select_category .= ">" . XoopsLocale::ALL . "</option>";
    foreach ($categories as $cid => $title) {
        $select_category .= "<option value=\"" . $cid . "\"";
        if ($cid = $by_cat) {
            $select_category .= "selected=\"selected\"";
        }
        $select_category .= ">" . $title . "</option>";
    }
    $select_category .= "</select>";
    $xoops->tpl()->assign('category_select', $select_category);
}

$index_banner = $xoops->getbanner();
$xoops->tpl()->assign('index_banner', $index_banner);
$index_code_place = $xoops->getModuleConfig('' . $moduleDirName . '_code_place');
$use_extra_code   = $xoops->getModuleConfig('' . $moduleDirName . '_use_code');
$use_banner       = $xoops->getModuleConfig('' . $moduleDirName . '_use_banner');
$index_extra_code = $xoops->getModuleConfig('' . $moduleDirName . '_index_code');
$xoops->tpl()->assign('use_extra_code', $use_extra_code);
$xoops->tpl()->assign('use_banner', $use_banner);
$xoops->tpl()->assign('index_extra_code', '<html>' . $index_extra_code . '</html>');
$xoops->tpl()->assign('index_code_place', $index_code_place);

$xoops->tpl()->assign('moduleDirName', $moduleDirName);

$cats  = $cattree->alumni_getFirstChild(0, $alumni_ids);
$count = 0;

foreach (array_keys($cats) as $i) {
    if (in_array($cats[$i]->getVar('cid'), $alumni_ids)) {
        $cat_img = $cats[$i]->getVar('img');
        if ($cat_img != 'http://') {
            $cat_img = XOOPS_URL . "/modules/{$moduleDirName}/images/cat/$cat_img";
        } else {
            $cat_img = '';
        }
        
        $listingHandler = $xoops->getModuleHandler('listing', 'alumni');
        $count_criteria         = new CriteriaCompo();
        $count_criteria->add(new Criteria('cid', $cats[$i]->getVar('cid'), '='));
        $count_criteria->add(new Criteria('valid', 1, '='));
        $count_criteria->add(new Criteria('cid', '(' . implode(', ', $alumni_ids) . ')', 'IN'));
        $listings = $listingHandler->getCount($count_criteria);

        $publishdate = isset($listings['date'][$cats[$i]->getVar('cid')]) ? $listings['date'][$cats[$i]->getVar('cid')] : 0;
        $all_subcats = $cattree->alumni_getAllChild($cats[$i]->getVar('cid'));
        if (count($all_subcats) > 0) {
            foreach (array_keys($all_subcats) as $k) {
                if (in_array($all_subcats[$k]->getVar('cid'), $alumni_ids)) {
                    $publishdate = (isset($listings['date'][$all_subcats[$k]->getVar('cid')]) AND $listings['date'][$all_subcats[$k]->getVar('cid')] > $publishdate) ? $listings['date'][$all_subcats[$k]->getVar('cid')] : $publishdate;
                }
            }
        }
    }
    $subcategories = array();

    $count++;

    $listingHandler = $xoops->getModuleHandler('listing', 'alumni');
    $listing_criteria       = new CriteriaCompo();
    $listing_criteria->add(new Criteria('cid', $cats[$i]->getVar('cid'), '='));
    $listing_criteria->add(new Criteria('valid', 1, '='));
    $listing_criteria->add(new Criteria('cid', '(' . implode(', ', $alumni_ids) . ')', 'IN'));
    $alumni_count = $listingHandler->getCount($listing_criteria);

    if (count($all_subcats) > 0) {
        foreach (array_keys($all_subcats) as $k) {

            if (in_array($all_subcats[$k]->getVar('cid'), $alumni_ids)) {
                $listingHandler = $xoops->getModuleHandler('listing', 'alumni');
                $sub_count_criteria     = new CriteriaCompo();
                $sub_count_criteria->add(new Criteria('cid', $all_subcats[$k]->getVar('cid'), '='));
                $sub_count_criteria->add(new Criteria('valid', 1, '='));
                $sub_count_criteria->add(new Criteria('cid', '(' . implode(', ', $alumni_ids) . ')', 'IN'));
                $alumni_subcount = $listingHandler->getCount($sub_count_criteria);

                if ($xoops->getModuleConfig('alumni_showsubcat') == 1 AND $all_subcats[$k]->getVar('pid') == $cats[$i]->getVar('cid')) { // if we are collecting subcat info for displaying, and this subcat is a first level child...
                    $subcategories[] = array('id' => $all_subcats[$k]->getVar('cid'), 'title' => $all_subcats[$k]->getVar('title'), 'count' => $alumni_subcount);
                }
            }
        }
    }

    if ($xoops->getModuleConfig('alumni_showsubcat') != 1) {
        unset($subcategories);

        $xoops->tpl()->append('categories', array(
            'image'     => $cat_img,
            'id'        => (int)($cats[$i]->getVar('cid')),
            'title'     => $cats[$i]->getVar('title'),
            'totalcats' => (int)($alumni_count),
            'count'     => (int)($count)));
    } else {
        $xoops->tpl()->append('categories', array(
            'image'         => $cat_img,
            'id'            => (int)($cats[$i]->getVar('cid')),
            'title'         => $cats[$i]->getVar('title'),
            'subcategories' => $subcategories,
            'totalcats'     => (int)($alumni_count),
            'count'         => (int)($count)));
    }
}
$xoops->tpl()->assign('total_confirm', '');

$listingHandler = $xoops->getModuleHandler('listing', 'alumni');

$xoops->tpl()->assign('moderated', false);
if ($xoops->getModuleConfig('' . $moduleDirName . '_moderated') == '1') {
    $xoops->tpl()->assign('moderated', true);
    $moderate_criteria = new CriteriaCompo();
    $moderate_criteria->add(new Criteria('valid', 0, '='));
    $moderate_criteria->add(new Criteria('cid', '(' . implode(', ', $alumni_ids) . ')', 'IN'));
    $moderate_rows = $listingHandler->getCount($moderate_criteria);
    $moderate_arr  = $listingHandler->getall($moderate_criteria);

    if ($xoops->isUser()) {
        if ($xoops->user->isAdmin()) {
            $xoops->tpl()->assign('user_admin', true);

            $xoops->tpl()->assign('admin_block', AlumniLocale::ADMIN_PANEL);
            if ($moderate_rows == 0) {
                $xoops->tpl()->assign('confirm_alumni', AlumniLocale::NO_LISTING_TO_APPROVE);
            } else {
                $xoops->tpl()->assign('confirm_alumni', AlumniLocale::THERE_ARE . " $moderate_rows  " . AlumniLocale::WAITING . "<br /><a href=\"admin/alumni.php?op=list_moderated\">" . constant($main_lang . '_SEEIT') . "</a>");
            }
	    $xoops->tpl()->assign('total_confirm', AlumniLocale::THIS_AND . " $moderate_rows " . AlumniLocale::WAITING);
        }
    }
}

$criteria = new CriteriaCompo();
$criteria->add(new Criteria('valid', 1, '='));
$criteria->add(new Criteria('cid', '(' . implode(', ', $alumni_ids) . ')', 'IN'));
$criteria->setLimit($xoops->getModuleConfig('' . $moduleDirName . '_per_page'));
$numrows = $listingHandler->getCount($criteria);

$xoops->tpl()->assign('total_listings', AlumniLocale::THERE_ARE . ' ' .$numrows. ' ' . AlumniLocale::ALUMNI_LISTINGS . ' ' . AlumniLocale::IN . ' ' .$numcat. ' ' . AlumniLocale::CATEGORIES);
$xoops->tpl()->assign('last_head', AlumniLocale::THE . ' ' . $xoops->getModuleConfig('' . $moduleDirName . '_newalumni') . ' ' . AlumniLocale::LASTADD);
$xoops->tpl()->assign('last_head_name', AlumniLocale::NAME_2);
$xoops->tpl()->assign('last_head_school', AlumniLocale::SCHOOL_2);
$xoops->tpl()->assign('last_head_studies', AlumniLocale::STUDIES_2);
$xoops->tpl()->assign('last_head_year', AlumniLocale::YEAR_2);
$xoops->tpl()->assign('last_head_date', XoopsLocale::DATE);
$xoops->tpl()->assign('last_head_local', AlumniLocale::TOWN_2);
$xoops->tpl()->assign('last_head_views', AlumniLocale::HITS);
$xoops->tpl()->assign('last_head_photo', AlumniLocale::PHOTO);

$listing_arr = $listingHandler->getall($criteria);

foreach (array_keys($listing_arr) as $i) {
    $lid        = $listing_arr[$i]->getVar('lid');
    $cid        = $listing_arr[$i]->getVar('cid');
    $name       = $listing_arr[$i]->getVar('name');
    $mname      = $listing_arr[$i]->getVar('mname');
    $lname      = $listing_arr[$i]->getVar('lname');
    $school     = $listing_arr[$i]->getVar('school');
    $year       = $listing_arr[$i]->getVar('year');
    $studies    = $listing_arr[$i]->getVar('studies');
    $activities = $listing_arr[$i]->getVar('activities');
    $extrainfo  = $listing_arr[$i]->getVar('extrainfo');
    $occ        = $listing_arr[$i]->getVar('occ');
    $date       = $listing_arr[$i]->getVar('date');
    $email      = $listing_arr[$i]->getVar('email');
    $submitter  = $listing_arr[$i]->getVar('submitter');
    $usid       = $listing_arr[$i]->getVar('usid');
    $town       = $listing_arr[$i]->getVar('town');
    $valid      = $listing_arr[$i]->getVar('valid');
    $photo      = $listing_arr[$i]->getVar('photo');
    $photo2     = $listing_arr[$i]->getVar('photo2');
    $view       = $listing_arr[$i]->getVar('view');

    $a_item        = array();
    $a_item['new'] = '';

    $newcount  = $xoops->getModuleConfig('' . $moduleDirName . '_countday');
    $startdate = (time() - (86400 * $newcount));
    if ($startdate < $date) {
        $newitem       = "<img src=\"" . XOOPS_URL . "/modules/{$moduleDirName}/images/newred.gif\" />";
        $a_item['new'] = $newitem;
    }

    $useroffset = '';
    if ($xoops->user) {
        $timezone = $xoops->user->timezone();
        if (isset($timezone)) {
            $useroffset = $xoops->user->timezone();
        } else {
            $useroffset = $xoopsConfig['default_TZ'];
        }
    }
    $date = ($useroffset * 3600) + $date;
    
    $date       = XoopsLocale::formatTimestamp($date, 's');
    
    if ($xoops->user) {
        if ($xoops->user->isAdmin()) {
            $a_item['admin'] = "<a href='admin/alumni.php?op=edit_listing&amp;lid=$lid&amp;cid=$cid'><img src='images/modif.gif' border=0 alt=\"" . AlumniLocale::MODADMIN . "\" /></a>";
        }
    }

    $a_item['name']    = "<a href='listing.php?lid=$lid'><b>$name&nbsp;$mname&nbsp;$lname</b></a>";
    $a_item['school']  = $school;
    $a_item['year']    = $year;
    $a_item['studies'] = $studies;
    $a_item['date']    = $date;
    $a_item['local']   = '';
    if ($town) {
        $a_item['local'] .= $town;
    }

    if ($photo) {
        $a_item['photo'] = "<a href=\"javascript:CLA('display-image.php?lid=$lid')\"><img src=\"" . XOOPS_URL . "/modules/{$moduleDirName}/images/photo.gif\" border=\"0\" width=\"15\" height=\"11\" alt='" . AlumniLocale::PHOTO_AVAILABLE . "' /></a>";
    } else {
    $a_item['photo'] = '';
    }

    $a_item['views'] = $view;

    $xoops->tpl()->append('items', $a_item);
}

Xoops::getInstance()->footer();
