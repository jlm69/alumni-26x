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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

include_once(XOOPS_ROOT_PATH . "/modules/alumni/class/alumni_tree.php");

class AlumniSearchForm extends XoopsThemeForm {
    /**
     * We are not using this for objects but we need to override the constructor
     * @param null $obj
     */
    public function __construct($obj = null) {
    }

    public function alumni_getSearchFrom($andor, $queries, $mids, $mid, $bycat) {
        $xoops  = Xoops::getInstance();
        $alumni = Alumni::getInstance();
        // create form
        parent::__construct(XoopsLocale::SEARCH, 'alumni', 'search.php', 'get');

        // create form elements
        $this->addElement(new XoopsFormText(XoopsLocale::KEYWORDS, 'query', 30, 255, htmlspecialchars(stripslashes(implode(' ', $queries)), ENT_QUOTES)), true);
        $type_select = new XoopsFormSelect(XoopsLocale::TYPE, 'andor', $andor);
        $type_select->addOptionArray(array(
          'AND' => XoopsLocale::ALL_AND, 'OR' => XoopsLocale::ANY_OR, 'exact' => XoopsLocale::EXACT_MATCH));
        $this->addElement($type_select);

        $by_cat = Request::getInt('by_cat', 0);

        $categoriesHandler = $xoops->getModuleHandler('category', 'alumni');
        $search = Search::getInstance();

        $module_id = $xoops->module->getVar('mid');
        $groups     = $xoops->isUser() ? $xoops->user->getGroups() : '3';
        $alumni_ids = $alumni->getGrouppermHandler()->getItemIds('alumni_view', $groups, $module_id);
        $criteria   = new CriteriaCompo();
        $criteria->add(new Criteria('cid', '(' . implode(', ', $alumni_ids) . ')', 'IN'));
        $criteria->setOrder($xoops->getModuleConfig('alumni_csortorder'));

        $category_arr = $categoriesHandler->getall($criteria);

        foreach (array_keys($category_arr) as $i) {
            $cid   = $category_arr[$i]->getVar('cid');
            $pid   = $category_arr[$i]->getVar('pid');
            $title = $category_arr[$i]->getVar('title');
        }

        $categories      = $alumni->getCategoryHandler()->getCategoriesForSearch();
        $category_select = new XoopsFormSelect(AlumniLocale::L_ALUMNI_CATEGORIES, 'by_cat', $by_cat);
        foreach ($categories as $cid => $title) {
            $category_select->addOption('0', XoopsLocale::ALL);
            $category_select->addOptionArray(array($cid => $title));
        }
        $this->addElement($category_select);


        if (!empty($mids)) {
            $mods_checkbox = new XoopsFormCheckBox(XoopsLocale::SEARCH_IN, 'mids[]', $mids);
        } else {
            $mods_checkbox = new XoopsFormCheckBox(XoopsLocale::SEARCH_IN, 'mids[]', $mid);
        }
        if (empty($modules)) {
            $gperm_handler     = $xoops->getHandlerGroupPermission();
            $available_modules = $gperm_handler->getItemIds('module_read', $xoops->getUserGroups());
            $available_plugins = Xoops\Module\Plugin::getPlugins('search');

            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('dirname', '(\'' . implode('\',\'', array_keys($available_plugins)) . '\')', 'IN'));
            if (isset($available_modules) && !empty($available_modules)) {
                $criteria->add(new Criteria('mid', '(' . implode(',', $available_modules) . ')', 'IN'));
            }
            $module_handler = $xoops->getHandlerModule();
            $mods_checkbox->addOptionArray($module_handler->getNameList($criteria));
        } else {
            /* @var $module XoopsModule */
            $module_array = array();
            foreach ($modules as $mid => $module) {
                $module_array[$mid] = $module->getVar('name');
            }
            $mods_checkbox->addOptionArray($module_array);
        }
        $this->addElement($mods_checkbox);
        if ($search->getConfig('keyword_min') > 0) {
            $this->addElement(new XoopsFormLabel(XoopsLocale::SEARCH_RULE, sprintf(XoopsLocale::EF_KEYWORDS_MUST_BE_GREATER_THAN, $search->getConfig('keyword_min'))));
        }
        $this->addElement(new XoopsFormHidden('action', 'results'));
        $this->addElement(new XoopsFormHiddenToken('id'));
        $this->addElement(new XoopsFormButton('', 'submit', XoopsLocale::SEARCH, 'submit'));

        return $this;
    }
}
