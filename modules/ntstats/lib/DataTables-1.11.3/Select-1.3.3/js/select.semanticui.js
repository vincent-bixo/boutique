/*! Semanic UI styling wrapper for Select
 * ©2018 SpryMedia Ltd - datatables.net/license
 */
(function(factory){if(typeof define==='function'&&define.amd){define(['jquery','datatables.net-se','datatables.net-select'],function($){return factory($,window,document)})}else if(typeof exports==='object'){module.exports=function(root,$){if(!root){root=window}
if(!$||!$.fn.dataTable){$=require('datatables.net-se')(root,$).$}
if(!$.fn.dataTable.select){require('datatables.net-select')(root,$)}
return factory($,root,root.document)}}else{factory(jQuery,window,document)}}(function($,window,document,undefined){return $.fn.dataTable}))