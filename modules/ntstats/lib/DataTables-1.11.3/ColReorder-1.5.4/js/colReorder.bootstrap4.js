/*! Bootstrap 4 styling wrapper for ColReorder
 * ©2018 SpryMedia Ltd - datatables.net/license
 */
(function(factory){if(typeof define==='function'&&define.amd){define(['jquery','datatables.net-bs4','datatables.net-colreorder'],function($){return factory($,window,document)})}else if(typeof exports==='object'){module.exports=function(root,$){if(!root){root=window}
if(!$||!$.fn.dataTable){$=require('datatables.net-bs4')(root,$).$}
if(!$.fn.dataTable.ColReorder){require('datatables.net-colreorder')(root,$)}
return factory($,root,root.document)}}else{factory(jQuery,window,document)}}(function($,window,document,undefined){return $.fn.dataTable}))