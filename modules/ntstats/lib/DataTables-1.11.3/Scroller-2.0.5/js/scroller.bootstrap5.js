/*! Bootstrap 5 styling wrapper for Scroller
 * ©2018 SpryMedia Ltd - datatables.net/license
 */
(function(factory){if(typeof define==='function'&&define.amd){define(['jquery','datatables.net-bs5','datatables.net-scroller'],function($){return factory($,window,document)})}else if(typeof exports==='object'){module.exports=function(root,$){if(!root){root=window}
if(!$||!$.fn.dataTable){$=require('datatables.net-bs5')(root,$).$}
if(!$.fn.dataTable.Scroller){require('datatables.net-scroller')(root,$)}
return factory($,root,root.document)}}else{factory(jQuery,window,document)}}(function($,window,document,undefined){return $.fn.dataTable}))