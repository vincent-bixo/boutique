<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

class WarehousePagination
{
	public $totalRows = '';
	public $perPage = 10;
	public $numLinks = 2;
	public $currentPage = 0;
	public $firstLink = '‹ First';
	public $nextLink = '<i class="icon-angle-right"></i>';
	public $prevLink = '<i class="icon-angle-left"></i>';
	public $lastLink = 'Last ›';
	public $curTagOpen ='<li class="active">';
	public $curTagClose ='</li>';
	public $firstTagOpen = '<li>';
	public $firstTagClose ='</li>';
	public $lastTagOpen ='<li>';
	public $lastTagClose ='</li>';
	public $nextTagOpen ='<li>';
	public $nextTagClose ='</li>';
	public $prevTagOpen ='<li>';
	public $prevTagClose ='</li>';
	public $numTagOpen ='<li>';
	public $numTagClose ='</li>';
	public $showInfos=true;
	public $currentOffset =0;

	public function __construct($params = array())
	{
		if (count($params)>0) {
			$this->initialize($params);
		}
	}

	public function initialize($params = array())
	{
		if (count($params)>0) {
			foreach ($params as $key=>$val) {
				if (isset($this->$key)) {
					$this->$key=$val;
				}
			}
		}
	}

	/**
	 * Generate the pagination links
	 */
	public function createPaginationLinks($onlyInfos = false)
	{
		// if total number of rows is zero, do not need to continue
		if ($this->totalRows == 0 || $this->perPage == 0) {
			return '';
		}

		// Calculate the total number of pages
		$numPages = ceil($this->totalRows/$this->perPage);

		// Is there only one page? will not need to continue
		if ($numPages == 1) {
			return '';
		}

		// Determine the current page
		if (!is_numeric($this->currentPage)) {
			$this->currentPage=0;
		}

		// Links content string variable
		$output='';

		// Showing links notification
		if ($this->showInfos && $onlyInfos) {
			$currentOffset=$this->currentPage;
			$infos='SHOWING '.($currentOffset + 1).' TO ';

			if (($currentOffset+$this->perPage)<($this->totalRows-1)) {
				$infos.=$currentOffset+$this->perPage;
			} else {
				$infos.=$this->totalRows;
			}
			$infos.=' OF '.$this->totalRows;
			return $infos;
		}

		$this->numLinks = (int)$this->numLinks;

		// Is the page number beyond the result range? the last page will show
		if ($this->currentPage > $this->totalRows) {
			$this->currentPage = ($numPages - 1) * $this->perPage;
		}

		$uriPageNum = $this->currentPage;

		$this->currentPage = floor(($this->currentPage / $this->perPage) + 1);

		// Calculate the start and end numbers.
		$start = (($this->currentPage - $this->numLinks) > 0) ? $this->currentPage - ($this->numLinks - 1) : 1;
		$end = (($this->currentPage + $this->numLinks) < $numPages) ? $this->currentPage + $this->numLinks : $numPages;

		// Render the "First" link
		if ($this->currentPage > $this->numLinks) {
			$output .= $this->firstTagOpen.$this->getAjaxLink('', $this->firstLink).$this->firstTagClose;
		}

		// Render the "previous" link
		if ($this->currentPage!=1) {
			$i=$uriPageNum-$this->perPage;
			if ($i==0) {
				$i='';
			}
			$output.=$this->prevTagOpen.$this->getAjaxLink($i,$this->prevLink).$this->prevTagClose;
		}

		// Write the digit links
		for ($loop=$start-1;$loop<=$end;$loop++) {
			$i = ($loop * $this->perPage) - $this->perPage;
			if ($i >= 0) {
				if ($this->currentPage == $loop) {
					$output .= $this->curTagOpen.$this->getAjaxLink('', $loop).$this->curTagClose; // current selected page number
				} else {
					$n = ($i == 0) ? '' : $i;
					$output .= $this->numTagOpen.$this->getAjaxLink($n, $loop).$this->numTagClose;
				}
			}
		}

		// Render the "next" link
		if ($this->currentPage < $numPages) {
			$output .= $this->nextTagOpen.$this->getAjaxLink($this->currentPage * $this->perPage, $this->nextLink).$this->nextTagClose;
		}

		// Render the "Last" link
		if (($this->currentPage + $this->numLinks) < $numPages) {
			$i=(($numPages*$this->perPage)-$this->perPage);
			$output.=$this->lastTagOpen.$this->getAjaxLink($i,$this->lastLink).$this->lastTagClose;
		}

		// Remove double slashes
		$output = preg_replace("#([^:])//+#", "\\1/", $output);

		// Add the wrapper HTML if exists
		return $output;
	}

	public function getAjaxLink($count, $text)
	{
        Context::getContext()->smarty->assign(array(
            'count' => ($count ? $count : 0),
            'text' => $text
        ));
        return Context::getContext()->smarty->createTemplate(
            _PS_MODULE_DIR_.'wkwarehouses/views/templates/admin/pagination_link.tpl',
            Context::getContext()->smarty
        )->fetch();
	}
}
