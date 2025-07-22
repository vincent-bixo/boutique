/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
var stats_chart;$(document).ready(function(){var ctx_sales=$('#ntstats_sales_chart');new Chart(ctx_sales,{type:'bar',data:{labels:labels,datasets:datasets_sales},options:{scales:{xAxes:[{ticks:{callback:function(value){return value.substr(0,1)},}}],}}});var ctx_nb_orders=$('#ntstats_nb_orders_chart');new Chart(ctx_nb_orders,{type:'bar',data:{labels:labels,datasets:datasets_nb_orders},options:{scales:{xAxes:[{ticks:{callback:function(value){return value.substr(0,1)},}}],yAxes:[{ticks:{min:0,precision:0}}]}}})});function listenerChart(){$('thead i').click(function(){var active_tab=$('#ntstats #nt_tab a.active');var active_tab_id=active_tab.attr('id');var block=$('#'+active_tab_id+'_content .stats_data');var title=$(this).parent().text().trim();var num_col=$(this).parent().index();var labels=[];var data=[];var tooltips=[];var back_colors=[];var border_colors=[];var id_chart=block.find('.data_chart canvas').attr('id');var type='line';var names=[];var color_names={};if($(this).hasClass(('fa-chart-pie'))){type='pie'}else if($(this).hasClass('fa-chart-line')){type='line'}else if($(this).hasClass('fa-chart-bar')){type='bar'}
block.find('tbody tr').each(function(){var label='';$(this).find('td.chart_label').each(function(){var text=$(this).text();if(label!==''){label+=' - '}
label+=text});labels.push(label)});if(type==='line'){back_colors=['rgba(0, 0, 0, 0)'];border_colors=getColor(1)}else{var list_color=getColor(labels.length);$.each(labels,function(key,label){var name='';var color='';var split_label=label.split(' - ');if(split_label[0].match(/[0-9]{4}-[0-9]{2}-[0-9]{2}/)||split_label[0].match(/[0-9]{4}-[0-9]{2}/)){split_label.splice(0,1)}
name=split_label.join(' - ');if(typeof name!==undefined&&name!==''){if(jQuery.inArray(name,names)>=0){color=color_names[name]}else{names.push(name);color=list_color[0];color_names[name]=color;list_color.splice(0,1)}}else{color=list_color[0];list_color.splice(0,1)}
back_colors.push(color);border_colors.push(color)})}
block.find('tbody tr td:nth-child('+(num_col+1)+')').each(function(){var dt_val=$(this).text();tooltips.push(dt_val);var dt_val_float=parseFloat($(this).text().replace(',','.').replace(/[^\d.-]/g,''));if(!isNaN(dt_val_float)){dt_val=dt_val_float}
data.push(dt_val)});if(block.find('thead th.reverse').length>0&&((block.find('.sorting_asc').length<=0&&block.find('.sorting_desc').length<=0)||(block.find('thead th.reverse').hasClass('sorting_asc')||block.find('thead th.reverse').hasClass('sorting_desc')))){labels.reverse();data.reverse();tooltips.reverse();back_colors.reverse();border_colors.reverse()}
drawChart('#'+id_chart,type,labels,data,tooltips,back_colors,border_colors,title)})}
function getColor(nb){let polychrome2n=['#3283FE','#FA0087','#1CBE4F','#FEAF16','#1C7F93','#B00068','#1CFFCE','#C4451C','#2ED9FF','#AA0DFE','#F8A19F','#325A9B','#1C8356','#B10DA1','#FBE426','#FC1CBF','#BDCDFF','#C075A6','#90AD1C','#782AB6','#F7E1A0','#66B0FF','#AAF400','#D85FF7','#822E1C','#B5EFB5','#683B79','#7ED7D1','#85660D','#3B00FB','#5A5156','#FE00FA','#66B0FF','#F6222E','#DEA0FD','#16FF32','#5899DA','#E8743B','#19A979','#ED4A7B','#945ECF','#13A4B4','#525DF4','#BF399E','#6C8893','#EE6868','#2F6497','#0000FF','#FF0000','#00FF00','#000033','#FF00B6','#005300','#FFD300','#009FFF','#9A4D42','#00FFBE','#783FC1','#1F9698','#FFACFD','#B1CC71','#F1085C','#FE8F42','#DD00FF','#201A01','#720055','#766C95','#02AD24','#C8FF00','#886C00','#FFB79F','#858567','#A10300','#14F9FF','#00479E','#DC5E93','#93D4FF','#004CFF','#FD3216','#00FE35','#6A76FC','#FED4C4','#FE00CE','#0DF9FF','#F6F926','#FF9616','#479B55','#EEA6FB','#DC587D','#D626FF','#6E899C','#00B5F7','#B68E00','#C9FBE5','#FF0092','#22FFA7','#E3EE9E','#86CE00','#BC7196','#7E7DCD','#FC6955','#E48F72','#2E91E5','#E15F99','#1CA71C','#FB0D0D','#DA16FF','#222A2A','#B68100','#750D86','#EB663B','#511CFB','#00A08B','#FB00D1','#FC0080','#B2828D','#6C7C32','#778AAE','#862A16','#A777F1','#620042','#1616A7','#DA60CA','#6C4516','#0D2A63','#AF0038',];let colorset=[];for(let num=0;num<Math.abs(nb);num++)
colorset.push(polychrome2n[num%polychrome2n.length]);return colorset}
function drawChart(id_chart,type,labels,data,tooltips,back_colors,border_colors,title){if(stats_chart){stats_chart.destroy();var chart_clone=$(id_chart).clone();var char_parent=$(id_chart).parent();$(id_chart).remove();char_parent.append(chart_clone)}
var ctx=$(id_chart);var options={};options.title={display:!0,text:title};options.tooltips={callbacks:{label:function(tooltipItem,data){var label=labels[tooltipItem.index];var tooltip=tooltips[tooltipItem.index];if(label){label+=': '}
label+=tooltip;return label}}};options.maintainAspectRatio=!1;options.legend={display:!1,};if(type==='bar'||type==='line'){options.scales={yAxes:[{ticks:{beginAtZero:!0}}]}}
stats_chart=new Chart(ctx,{type:type,data:{labels:labels,datasets:[{data:data,borderColor:border_colors,backgroundColor:back_colors,}]},options:options})}