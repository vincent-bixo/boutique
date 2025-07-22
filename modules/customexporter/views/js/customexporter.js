/**
*  NOTICE OF LICENSE
* 
*  Module for Prestashop
*  100% Swiss development
* 
*  @author    Webbax <contact@webbax.ch>
*  @copyright -
*  @license   -
*/

$(document).ready(function(){
    $("#export_type").change(function(){
        var export_type = $("#export_type").val();
        if(export_type!=''){
            liste_fields(export_type); // pour afficher la liste
            $(".export_form").hide();  // masque tous les forms
            $("#"+export_type).show(); // affiche le form de l'export sélectionné
        }else{
            $("#order").hide();
            $("#order_detail").hide();
            $("#customer").hide();
            $("#product").hide();
        }
    });
});

// listes les champs
function liste_fields(export_type){
    var base_url_ajax = $("#base_url_ajax").val();
    $("#fields_"+export_type).empty(); // vide le div
    $("#loading_"+export_type).show();
    $.get(base_url_ajax+'modules/customexporter/ajax.php',{
        action:'list_fields',
        export_type:export_type,
        id_shop:$('#id_shop').val(),
        token_module:$('#token_module').val(),
    },function(data){ // lors de la réception des données via echo json_encode
           var form_cols = '<table>';
           var data_req = $.parseJSON(data);
           
           /* alert token */
           if(data_req.error!='' && data_req.error!='undefined' && data_req.error!=undefined){
               var msg = data_req.error;
               alert(msg);
           }
           
           var nb_fields = data_req.length;
                $.each(data_req,function(i,v){ // foreach le tableau complet des champs
                    form_cols = form_cols+'<tr>';
                                if(v.place!=1){ // si c'est la colonne 1 on affiche pas le bouton monter
                                    form_cols = form_cols+
                                        '<td>'+
                                            '<div id="field_'+export_type+'_id'+v.place+'" style="display:none">'+v.id_customexporter+'</div>'+ // div pour deviner l'id
                                            '<div id="field_'+export_type+'_link_up'+v.place+'">'+   // pour modifier le lien
                                                link_arrow(v.id_customexporter,v.place,v.export_type,'up')+
                                            '</div>'+
                                        '</td>';
                                }else{
                                    form_cols = form_cols+'<td></td>';                   
                                }
                                if(v.place!=nb_fields){ // si c'est la dernière colonne on affiche pas le bouton descendre
                                   form_cols = form_cols+
                                        '<td>'+
                                            link_arrow(v.id_customexporter,v.place,v.export_type,'down')+
                                        '</td>';
                                }else{
                                   form_cols = form_cols+'<td></td>';
                                }
                                var checked = 0;
                                if(v.checked==1){checked='checked';}else{checked='';}
                                form_cols = form_cols+'<td><input id="checkbox_order'+v.place+'" type="checkbox" name="fields_no[]" value="'+v.field_no+'" '+checked+' /></td>'+
                                    '<td>'+
                                        '<div id="field_'+export_type+'_name'+v.place+'"> '+v.field_name+'</div>'+
                                    '</td>'+
                                '</tr>'
           });
           form_cols = form_cols+'</table>';
           $("#fields_"+export_type).append(form_cols);
           $("#loading_"+export_type).hide();
    });
    return;
}

// monte d'un cran
function move_up(id,place,export_type){
    // Modifie la position dans la base de donnée
    if(id>0){
        $.get('../modules/customexporter/ajax.php',{
            id:id, 
            action:'move_up',
            place:place,
            export_type:export_type,
            id_shop:$('#id_shop').val(),
            token_module:$('#token_module').val(),
        }, function(data){});
    }

    // Modifie l'affichage uniquement
    // -----------------------------------------------------------------
    // Part 1
    // Récupére les descriptions
    var div_name_select = $("#field_"+export_type+"_name"+place).html();
    var place_up = place-1;
    var div_name_dest = $("#field_"+export_type+"_name"+place_up).html();
    // Vide les descriptions
    $("#field_"+export_type+"_name"+place).empty();
    $("#field_"+export_type+"_name"+place_up).empty();
    // Remplace le contenu de chaque div (inversion)
    $("#field_"+export_type+"_name"+place).append(div_name_dest);
    $("#field_"+export_type+"_name"+place_up).append(div_name_select);

    // Part 2
    // Récupère les id des deux div
    var id_select = $("#field_"+export_type+"_id"+place).html();
    var id_dest = $("#field_"+export_type+"_id"+place_up).html();
    if(id_dest==null){id_dest=1;}
    if(id_select==null){id_select=1;}
    // Crée deux nouveau liens pour le div sélectionné
    var link_up_select = link_arrow(id_dest,place,export_type,'up');
    var link_down_select = link_arrow(id_dest,place,export_type,'down');
    // Crée deux nouveau liens pour le div de destination
    var link_up_dest = link_arrow(id_select,place_up,export_type,'up');
    var link_down_dest = link_arrow(id_select,place_up,export_type,'down');

    // Vide les div select et destination
    // select
    $("#field_"+export_type+"_id"+place).empty();
    $("#field_"+export_type+"_link_up"+place).empty();
    $("#field_"+export_type+"_link_down"+place).empty();
    // destination
    $("#field_"+export_type+"_id"+place_up).empty();
    $("#field_"+export_type+"_link_up"+place_up).empty();
    $("#field_"+export_type+"_link_down"+place_up).empty();

    // Remplace avec les nouvelles valeurs
    // select
    $("#field_"+export_type+"_id"+place).append(id_dest);
    $("#field_"+export_type+"_link_up"+place).append(link_up_select);
    $("#field_"+export_type+"_link_down"+place).append(link_down_select);
    // destination
    $("#field_"+export_type+"_id"+place_up).append(id_select);
    $("#field_"+export_type+"_link_up"+place_up).append(link_up_dest);
    $("#field_"+export_type+"_link_down"+place_up).append(link_down_dest);

    // Récupère les 2 checkbox
    var checked_select = $("#checkbox_order"+place).is(':checked');
    var checked_dest = $("#checkbox_order"+place_up).is(':checked');
    var checkbox_val_select = $('input[id=checkbox_order'+place+']').val();
    var checkbox_val_dest = $('input[id=checkbox_order'+place_up+']').val();

    // Remplace les checkbox
    $("#checkbox_order"+place).attr('checked',checked_dest);
    $("#checkbox_order"+place_up).attr('checked', checked_select);
    $('input[id=checkbox_order'+place+']').val(checkbox_val_dest);
    $('input[id=checkbox_order'+place_up+']').val(checkbox_val_select);
}

// descend d'un cran
function move_down(id,place,export_type){
    // Modifie la position dans la base de donnée
    if(id>0){
        $.get('../modules/customexporter/ajax.php',{
            id:id,
            action:'move_down',
            place:place,
            export_type:export_type,
            id_shop:$('#id_shop').val(),
            token_module:$('#token_module').val(),
        }, function(data){});
    }
    // Modifie l'affichage uniquement
    // -----------------------------------------------------------------
    // Part 1
    // Récupére les descriptions
    var div_name_select = $("#field_"+export_type+"_name"+place).html();
    var place_down = place+1;
    var div_name_dest = $("#field_"+export_type+"_name"+place_down).html();
    // Vide les descriptions
    $("#field_"+export_type+"_name"+place).empty();
    $("#field_"+export_type+"_name"+place_down).empty();
    // Remplace le contenu de chaque div (inversion)
    $("#field_"+export_type+"_name"+place).append(div_name_dest);
    $("#field_"+export_type+"_name"+place_down).append(div_name_select);

    // Part 2
    // Récupère les id des deux div
    var id_select = $("#field_"+export_type+"_id"+place).html();
    var id_dest = $("#field_"+export_type+"_id"+place_down).html();
    if(id_dest==null){id_dest=1;}
    if(id_select==null){id_select=1;}
    // Crée deux nouveau liens pour le div sélectionné
    var link_up_select = link_arrow(id_dest,place,export_type,'up');
    var link_down_select = link_arrow(id_dest,place,export_type,'down');
    // Crée deux nouveau liens pour le div de destination
    var link_up_dest = link_arrow(id_select,place_down,export_type,'up');
    var link_down_dest = link_arrow(id_select,place_down,export_type,'down');

    // Vide les div select et destination
    // select
    $("#field_"+export_type+"_id"+place).empty();
    $("#field_"+export_type+"_link_up"+place).empty();
    $("#field_"+export_type+"_link_down"+place).empty();
    // destination
    $("#field_"+export_type+"_id"+place_down).empty();
    $("#field_"+export_type+"_link_up"+place_down).empty();
    $("#field_"+export_type+"_link_down"+place_down).empty();

    // Remplace avec les nouvelles valeurs
    // select
    $("#field_"+export_type+"_id"+place).append(id_dest);
    $("#field_"+export_type+"_link_up"+place).append(link_up_select);
    $("#field_"+export_type+"_link_down"+place).append(link_down_select);
    // destination
    $("#field_"+export_type+"_id"+place_down).append(id_select);
    $("#field_"+export_type+"_link_up"+place_down).append(link_up_dest);
    $("#field_"+export_type+"_link_down"+place_down).append(link_down_dest);

     // Récupère les 2 checkbox
    var checked_select = $("#checkbox_order"+place).is(':checked');
    var checked_dest = $("#checkbox_order"+place_down).is(':checked');
    var checkbox_val_select = $('input[id=checkbox_order'+place+']').val();
    var checkbox_val_dest = $('input[id=checkbox_order'+place_down+']').val();
    // Remplace les checkbox
    $("#checkbox_order"+place).attr('checked',checked_dest);
    $("#checkbox_order"+place_down).attr('checked', checked_select);
    $('input[id=checkbox_order'+place+']').val(checkbox_val_dest);
    $('input[id=checkbox_order'+place_down+']').val(checkbox_val_select);
}

// lien montant ou descendant
function link_arrow(id,place,export_type,direction){
    var img = '';
    if(place==1 && direction=='up'){img='';}else{img='<img src="../modules/customexporter/views/img/'+direction+'.gif"/>';}
    return '<a href="javascript:move_'+direction+'('+id+','+place+',\''+export_type+'\')">'+img+'</a>';
}
