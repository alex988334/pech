function OnClick_GetRegNum(setID, nodeId, project, typeID, subTypeID, direction, contractorCode) {
    var typeValue = $('#' + typeID).val();
    var subTypeIDValue = $('#' + subTypeID).val();

    $.getJSON('', {
        'func': 'zsabrisutils.GenerateRegNum',
        'nodeId': nodeId,
        'Project': project,
        'Type': typeValue,
        'SubType': subTypeIDValue,
        'Direction': direction,
        'ContractorCode': contractorCode
    }, function (data) {
        if (data.ok) {
            $('#' + setID).val(data.data);
        } else {
            alert(data.errMsg);
        }
    });
}


function fillRegDate() {
    var $regDateHidden = $("#_1_1_33_1");
    if ($regDateHidden.val() == undefined || $regDateHidden.val().length == 0) {

        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1; //January is 0!
        var yyyy = today.getFullYear();

        if (dd < 10) dd = '0' + dd
        if (mm < 10) mm = '0' + mm

        today = dd + '/' + mm + '/' + yyyy;
        $regDateHidden.attr("value", "D/" + yyyy + "/" + mm + "/" + dd + ":0:0:0");
        $('#_datepicker__1_1_33_1').attr("value", dd + '/' + mm + '/' + yyyy);
    }
}




function SendFormWithoutReload(cb) {
    if (document.myForm.LL_FUNC_VALUES.value.match('Form.SubmitForm') && !document.myForm.LL_FUNC_VALUES.value.match('formwf')) {
        document.myForm.LL_FUNC_VALUES.value = document.myForm.LL_FUNC_VALUES.value.replace(new RegExp(/Form.SubmitForm/), "Form.SaveForm");

    } else if (document.myForm.LL_FUNC_VALUES.value.match('formwf')) {

        document.myForm.LL_FUNC_VALUES.value = document.myForm.LL_FUNC_VALUES.value.replace(new RegExp(/formwf.submitform/), "formwf.saveform");

    }

    var regex = document.myForm.LL_FUNC_VALUES.value.match(new RegExp(/'nextUrl'='.*?'/i))
    if(regex && regex.length > 0){
        var saved_next_url = [0]
        document.myForm.LL_FUNC_VALUES.value = document.myForm.LL_FUNC_VALUES.value.replace(new RegExp(/'nextUrl'='.*?'/i), "'nextUrl'='/otcs/cs.exe?func=llworkspace'");
    }

    var jForm = $(document.myForm);
   
    $.ajax({
        method: "POST",
        url: jForm.attr('action'),
        data: jForm.serialize(),// decodeURIComponent(jForm.serialize()),
        type: "POST",
        success: function (msg, type, info) {
            if(saved_next_url)
                document.myForm.LL_FUNC_VALUES.value = document.myForm.LL_FUNC_VALUES.value.replace(new RegExp(/'nextUrl'='.*?'/i), saved_next_url);

            console.log("type", type)
            console.log("info", info)

            toogleLoader({ show: false });


            if (info.status == 500) {
                ShowMessage('error', 'Ошибка!', ['Статус ответа сервера <strong>500</strong>']);
            } else {
                ShowMessage('message', 'Сохранено!');
            }

            if (window.LL_FUNC_VALUES) {
                document.myForm.LL_FUNC_VALUES.value = window.LL_FUNC_VALUES;
                window.LL_FUNC_VALUES = null;
            }

            if ($.isFunction(cb)) {
                cb.call(arguments)
            }
        }
    });
}
function OnClick_ParceRegNum(nodeId, regNumID, project, contractorCode) {
    var regNum = $('#' + regNumID).val();
    $.getJSON('', {
        'func': 'zsabrisutils.ParseAndFill',
        'nodeId': nodeId,
        'regNum': regNum,
        'projectCode': project,
        'contractorCode': contractorCode
    }, function (data) {
        if (data.ok) {
            location.reload(true);
        } else {
            alert(data.errMsg);
        }
    });
}

var linkDocs = {
    addDocs: new Array(0),
    addEngDocs: new Array(0),
    delDocs: new Array(0),

    getLinkDocs: function (typeDoc, url, nodeID, callback, element) {

        if (typeDoc == 'engDoc') {
            linkDocs.addEngDocs.push(+nodeID);
        } else {
            linkDocs.addDocs.push(+nodeID);
        }

        var i = linkDocs.delDocs.indexOf(+nodeID);
        if (i != -1) {
            linkDocs.addDocs = linkDocs.addDocs.slice(i, 1);
        }

        if (callback) {
            return callback(url, element);
        } else {
            return nodeID;
        }
    },

    getBySearch: function (typeDoc, url, objectid, callback, seachObjID) {
        var changeInterval;
        var windowOpts = 'height=640,width=800,left=' + ((screen.width / 2) - (400)) + ',top=' + ((screen.height / 2) - (320)) + ',resizable=yes,menubar=no,scrollbars=yes,toolbar=yes';
        this.w = window.open('?func=ll&objaction=SimpleSearchTargetBrowse&objId=' + seachObjID, 'SimpleSearch', windowOpts);

        var win = this.w;
        var modFunc = this.getLinkDocs;

        function changeResultAction() {
            if (win.SelectItem) {
                win.SelectItem = function (nodeID) {
                    modFunc(typeDoc, url, nodeID, callback, objectid);
                    win.close();
                    //   $(".avAShowOther").click();
                };
                win.sc_partnerHook = true;
            }
            if (win.closed) {
                clearInterval(changeInterval);
            }
        }
        changeInterval = setInterval(changeResultAction, 100);
    }
}

function OnClick_AddDoc(typeDoc, url, objectid, seachObjID) {
    linkDocs.getBySearch(typeDoc, url, objectid, UpdateLink, seachObjID);
}

function UpdateLink(url, objectid) {
    var dateSend = $('#_1_1_121_1').val();
    $.post(url + '/open/WRFormLinks', {
        objectid: objectid,
        addNodeDoc: (linkDocs.addDocs.length > 0) ? '[' + linkDocs.addDocs.join('],[') + ']' : '',
        addNodeEngDoc: (linkDocs.addEngDocs.length > 0) ? '[' + linkDocs.addEngDocs.join('],[') + ']' : '',
        delDocs: (linkDocs.delDocs.length > 0) ? '[' + linkDocs.delDocs.join('],[') + ']' : '',
        DateSend: dateSend
    }, function (data) {
        //$('#LinkedDoc .avDivOuter').remove();
        $('#LinkedDoc .avDivOuter').empty().append(data).find(".avAShowOther").click();;
        // $
    });
}

function show_rest(objectsName) {
    $(objectsName).css('display', 'inline');
}

function delLinkRowSubmit(url, objectid, id, question) {
    if (confirm(question)) {
        var delValue = +$('#' + id).val();
        var i = linkDocs.addDocs.indexOf(delValue);
        var isNewEl = false;
        if (i != -1) {
            linkDocs.addDocs.splice(i, 1);
            isNewEl = true;
        }
        var i = linkDocs.addEngDocs.indexOf(delValue);
        if (i != -1) {
            linkDocs.addEngDocs.splice(i, 1);
            isNewEl = true;
        }
        if (!isNewEl) {
            linkDocs.delDocs.push(delValue);
        }

        UpdateLink(url, objectid);
    }
}

/**
 * Show/hide loader view
 * @param {{show: boolean}} options 
 */
function toogleLoader(options) {
    if (window.loaderView == undefined) {
        window.loaderView = $("<div></div>");
        window.loaderView.addClass("loaderView");
    }
    if (options.show) {
        $("body").append(window.loaderView);
    } else {
        window.loaderView.remove();
        window.loaderView = null;

    }
}

function isLoaderActive(){
    return (window.loaderView != undefined);
}


function renameAttaches(onComplete){
    var DocumentsName = $('.documents-wrapper [ischanged="true"]');
    var completed = 0
    if (DocumentsName.length) {
        DocumentsName.each(function (i, item) {
            var targetInput = $(item);
            var attachId = $('#' + targetInput.attr('attach-id'))

            $.ajax({
                type: "PUT",
                url: projectParams.urlWL + "/api/v1/nodes/" + attachId.val(),
                headers: {
                    otcsticket: getCookie('LLCookie')
                },
                data: "name=" + targetInput.val(),
                success: function (msg) {
                    completed++;
                }
            });
        });


        var timerId = setInterval(function(){
            if(completed == DocumentsName.length){
                clearInterval(timerId);
                if(onComplete) onComplete();
            }
        }, 200);

    }else{
        if(onComplete) onComplete();
    }

}


function makeRevNone(){

    $("input[fieldname=REVISION]").each(function(i, el){
        var $revInput = $(el);
        console.log("REV VALUE", $revInput.attr("name"), $revInput.val());
        if($revInput.val() == null || $revInput.val().length == 0) $revInput.attr("value", "<None>");
    })

   
}

 
function saveLinksFix(){
    if($("#_1_1_123_1").length == 0){
        $("#_1_1_123_1_fix").attr("name", "_1_1_123_1");
    }else{
        $("#_1_1_123_1_fix").attr("name", "");
    }

    if($("#_1_1_87_1").length == 0){
        $("#_1_1_87_1_fix").attr("name", "_1_1_87_1");
    }else{
        $("#_1_1_87_1_fix").attr("name", "");
    }

}

function fixCheckboxValues(){
    console.log("fixCheckboxValues");
    $("input[type=checkbox]").each(function(i, el){
        if($(el).val() == "on") $(el).attr("value", "1");
        else if ($(el).val() == "off") $(el).attr("value", "0");
    })
}

function removeEmptyFields(){
    console.log("removeEmptyFields");
    $("input[name='']").remove();
}


function Click_Save(theForm, submitForm, notRedirect) {
    console.log("Click_Save");

    var oDate = new Date();
    var aMonth = [
        'Январь',
        'Февраль',
        'Март',
        'Апрель',
        'Май',
        'Июнь',
        'Июль',
        'Август',
        'Сентябрь',
        'Октябрь',
        'Ноябрь',
        'Декабрь',
    ]
    $('[name=_1_1_165_1').val(oDate.getFullYear());
    $('[name=_1_1_122_1').val((oDate.getMonth().toString().length > 1 ? oDate.getMonth().toString() : '0' + (oDate.getMonth() + 1).toString() + '. ') + aMonth[oDate.getMonth()]);

    removeEmptyDocsRows();
    makeRevNone()
    saveLinksFix()
    fixCheckboxValues()
    removeEmptyFields()

    toogleLoader({ show: true });

    renameAttaches(function(){

        window.LL_FUNC_VALUES = theForm.LL_FUNC_VALUES.value;

        if (submitForm == null || !submitForm) {
            theForm.LL_FUNC_VALUES.value = theForm.LL_FUNC_VALUES.value.replace(new RegExp(/'LL_Editable'=false/i), "'LL_Editable'=true");
            var regMatch = myForm.LL_FUNC_VALUES.value.match(new RegExp(/'func'='formwf.saveform'/i));
            if (regMatch != null && regMatch.length > 0) {
    
                regMatch = location.href.match(new RegExp(/NextURL=(.*)/i));
    
                console.log(regMatch);
    
                if (regMatch != null && regMatch.length == 2) {
                    var url = location.href.replace(regMatch, "");
                    theForm.LL_FUNC_VALUES.value = theForm.LL_FUNC_VALUES.value.replace(new RegExp(/'LL_NextURL'='.*?'/i), "'LL_NextURL'='" + decodeURIComponent(url) + "'");
                }
            }
            else {
                theForm.LL_FUNC_VALUES.value = theForm.LL_FUNC_VALUES.value.replace(new RegExp(/Form.SubmitForm/), "Form.SaveForm");
            }
        } else {
            theForm.LL_FUNC_VALUES.value = theForm.LL_FUNC_VALUES.value.replace(new RegExp(/Form.SaveForm/), "Form.SubmitForm");
        }
    
    
    
        // return;
    
        if ($.isFunction(notRedirect)) {
            SendFormWithoutReload(notRedirect);
        } else {
    
        }
    })

    
}


function removeEmptyDocsRows() {
    //return;
    $(".document-item").each(function (i, docRow) {
        var $docRow = $(docRow);
        var $albumSelect = $docRow.find("[fieldname=NUM]");
        var initValue = $albumSelect.attr("initial-value");

        var attachIdExist = $docRow.find("[fieldname=ATTACHID]").val() != null && $docRow.find("[fieldname=ATTACHID]").val().length > 0;
        var revisionExist = $docRow.find("[fieldname=REVISION]").val() != null && $docRow.find("[fieldname=REVISION]").val().length > 0;
        var initValueExist = initValue != null && initValue.length > 0;
        var numExist = $albumSelect.val() != null && $albumSelect.val().length > 0;

        if (!attachIdExist && !revisionExist && !initValueExist && !numExist) $docRow.remove();

    })
}

function Click_Reject(sUrl, iNodeId, sNextUrl) {
    $('[name=_1_1_8_1]').val('Отклонено');
    $.get(sUrl + '/open/RejectDocWR', {
        formDataID: iNodeId
    }, function (data) {
        document.location = sNextUrl;
    });
}

function Click_Outgoing(url, iNodeId) {
    console.log("Click_Outgoing");
    var state = $("#_1_1_8_1").val();
    if (state.toLowerCase() == "регистрация") {
        window.open(url + '/open/NewOutgoingDocWR?answer=1&objectid=' + iNodeId, '_blank');
    } else {
        window.open(url + '/open/NewOutgoingDocWR?objectid=' + projectParams.ProjectCode, '_blank');
    }

}

/**
 * Show last attributes in engeneer documents
 */
function showLastAttributes() {

    $(".document-item").each(function (i, item) {
        $(item).find(".last-attributes:first").css({
            "display": "table"
        });
    });

}



function markDirty(oDOMElement) {
    var nameFormComponent = $(oDOMElement).attr('name');

    if ($(oDOMElement).attr('fieldname') == 'NAME') {
        $(oDOMElement).attr('ischanged', true);
    }

    switch (nameFormComponent) {
        case 'GetPM':
            checkWithEngDoc($(oDOMElement));
            break;
        case '_1_1_143_1':
            needAnswer($(oDOMElement));
            break;
        case 'WithEngDoc':
            toogleDocsTable($(oDOMElement));
            break;
        case "_1_1_55_1":
            $("#_1_1_52_1").empty();
            $("#_1_1_52_1").addClass("loading-state");
            loadOptions("_1_1_52_1", [$("#_1_1_55_1").attr("value")]);
            break;
        case "_1_1_52_1":

            var datePerform = $("#_1_1_97_1");
            var typeValue = $("#_1_1_55_1").attr('value');
            var subtypeValue = $("#_1_1_52_1").attr('value');

            if (datePerform.val() == undefined || datePerform.val().length == 0) {
                if (typeValue != undefined && typeValue.length > 0 && subtypeValue != undefined && subtypeValue.length > 0) {
                    $("#_datepicker__1_1_97_1").attr("value", "Загрузка");
                    $("#_datepicker__1_1_97_1").datepicker("disable");
                    loadOptions("_1_1_97_1", [typeValue, subtypeValue]);
                }
            } else {
                fillDate("_1_1_97_1");
            }

            break;
        case "_1_1_81_1":
            loadOptions("DM", [$(oDOMElement).val()]);
            break;

        default:
            break;
    }

    if ($(oDOMElement).hasClass("attach-field")) {

        var nameField = $("[attach-id=" + $(oDOMElement).attr("ID") + "]");
        var text = $(oDOMElement).find("option:selected").text();

        if (text.length > 0 && $(oDOMElement).val().length > 0) {
            nameField.removeAttr("readonly");
            nameField.attr("value", $(oDOMElement).find("option:selected").text());
        } else {
            nameField.attr("readonly", "true");
            nameField.attr("value", "Сначала выберите вложение");
        }


    } else if ($(oDOMElement).hasClass("list-field")) {

        if (projectParams.docsData != undefined) {
            var needArr = projectParams.docsData[$(oDOMElement).attr("id")];

            if (needArr != undefined) {

                var needDict = needArr.find(function (e) {
                    return e.list == $(oDOMElement).attr("value");
                });

                if (needDict != undefined) {
                    $("#" + needDict.cdid.id).attr("value", needDict.cdid.value);
                    $("#" + needDict.mdlperformer.id).attr("value", needDict.mdlperformer.value);
                    $("#" + needDict["mdlperformer-fio"].id).attr("value", needDict["mdlperformer-fio"].value);
                }
            }
        }
    } else if ($(oDOMElement).attr("mark") == "SubStatus") {

        var revFieldID = $(oDOMElement).closest(".document-item").find("[mark=Revision]").attr("id");
        //  FilterSelect.aFilters[revFieldID].setPlomb(true);

        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCSUBSTATUS, [projectParams.ProjectCode], function (rows) {
            var selectedSubStatus = rows.find(function (row) {
                return row.SUBMISSION_STATUS == $(oDOMElement).val();
            });
            console.log(selectedSubStatus);

            if (selectedSubStatus != undefined && selectedSubStatus.NEED_REVISION == "1") {
                LookupManager.get(LookupManager.FUNCTIONS.ZEDM_REVISION, [projectParams.ProjectCode, $(oDOMElement).val()], function (data) {


                    //   FilterSelect.aFilters[revFieldID].setPlomb(false);

                    $("#" + revFieldID).find("ul").empty();
                    var filter = new FilterSelect({
                        id: revFieldID,
                        data: data,
                        showValList: 'REVISION'
                    }, "select");


                });
            }

        });

    }

}

/**
 * Show or hide documents table
 * @param {jQuery} oElement Show or hide documents table checkbox
 */
function toogleDocsTable(oElement) {
    if (oElement.is(':checked')) {
        $(".documents-wrapper").show();
    } else {
        $(".documents-wrapper").hide();
    }
}

function needAnswer(oElement) {
    if (oElement.is(':checked')) {

        var typeValue = $("#_1_1_55_1").attr('value');
        var subtypeValue = $("#_1_1_52_1").attr('value');
        var datePickerValue = $('#_datepicker__1_1_93_1').attr('value');

        var typeIsFilled = typeof typeValue !== typeof undefined && typeValue !== false && typeValue.length > 0;
        var subTypeIsFilled = typeof subtypeValue !== typeof undefined && subtypeValue !== false && subtypeValue.length > 0;
        var datePickerIsFilled = typeof datePickerValue !== typeof undefined && datePickerValue !== false && datePickerValue.length > 0;

        $('[name=_1_1_93_1]').attr("value", "");
        $('[name=_1_1_93_1_dirtyFlag]').attr("value", "0");
        $('[name=_1_1_93_1_hour]').attr("value", "0");
        $('[name=_1_1_93_1_minute]').attr("value", "0");
        $('[name=_1_1_93_1_second]').attr("value", "0");
        $('[name=_1_1_93_1_ampm]').attr("value", "0");
        $("#_1_1_143_1_Exists").attr("value", "1");

        if (subTypeIsFilled && typeIsFilled && !datePickerIsFilled)
            loadOptions("_1_1_93_1", [typeValue, subtypeValue]);
        else {
            $("#_datepicker__1_1_93_1").datepicker("enable");
            $('#_datepicker__1_1_93_1').attr("value", "");
        }


    } else {
        $("#_datepicker__1_1_93_1").datepicker("disable");

        $('#_datepicker__1_1_93_1').attr("value", "");
        $('#_datepicker__1_1_54_1').attr("value", "");
        $("#_1_1_143_1_Exists").attr("value", "0");

        $('[name=_1_1_93_1]').attr("value", "");
        $('[name=_1_1_93_1_dirtyFlag]').attr("value", "1");
        $('[name=_1_1_93_1_hour]').attr("value", "0");
        $('[name=_1_1_93_1_minute]').attr("value", "0");
        $('[name=_1_1_93_1_second]').attr("value", "0");
        $('[name=_1_1_93_1_ampm]').attr("value", "0");


    }



}



function loadFolders() {
    var baseURL = "/otcs/cs.exe";

    $.get(baseURL, { func: "zsabrisutils.BrowseView", objId: window.folderObjectID }, function (data) {

        var documentElement = document.createElement('html');

        documentElement.innerHTML = data;

        var jDocumentElement = $(documentElement);

        jDocumentElement.find('body #MultiOperationBar1 > script').remove();

        $("#folderBrowser").append(jDocumentElement.find("body"));



    });
}

function nms_menuStart(myDoc){
    var s = nm_add_new_menu_top;

	s = s.replace( /#IMG#/g, nm_img );
	s = s.replace( /#COLSPAN#/g, nm_colspan );

    myDoc.write( s );
}

function nms_AddNewMenuEnd(myDoc){
    var s = nm_add_new_menu_end;
	myDoc.write( s );
}

function nms_createObjectItemWithUrl( myDoc, itemAlt, itemImg, itemUrl )
{
	var s = nm_create_object_item;
	var itemIconClass = itemImg.substr(itemImg.lastIndexOf("/") + 1, itemImg.indexOf(".") - itemImg.lastIndexOf("/") - 1)

	s = s.replace( /#IMG#/g, nm_img );
	s = s.replace( /#ITEMURL#/g, itemUrl );
	s = s.replace( /#ALTTEXT#/g, itemAlt );
	s = s.replace( /#ITEMIMG#/g, itemImg );
	s = s.replace(/#STYLE#/g, nm_style);
	s = s.replace(/#ITEMICON#/g, itemIconClass);

	myDoc.write( s );
}

function configFolders(myDoc) {

    nm_setColSpan("7");
    nm_setSupportPath("/img/");
    nms_menuStart(myDoc);
    nm_setStyle("");

    myDoc.write("<div class=\"toolbar-background\">");
/*
    if (window.File && window.FileList) {
        nm_dndSupport();

        $(function () {
            $("#dndsupportenabled").tooltip({
                position: {
                    my: "center bottom-20",
                    at: "center top",
                    using: function (position, feedback) {
                        $(this).css(position);
                        $("<div>")
                            .addClass("arrow")
                            .addClass(feedback.vertical)
                            .addClass(feedback.horizontal)
                            .appendTo(this);
                    }
                }
            });
        });
    }*/
   nms_createObjectItemWithUrl(myDoc, "Add Document", "/img/webdoc/ico-doc-add.gif", "/otcs/cs.exe?func=ll&objType=144&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022");
  nms_createObjectItemWithUrl(myDoc, "Add Folder", "/img/webdoc/ico-folder-add.gif", "/otcs/cs.exe?func=ll&objType=0&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022");
  myDoc.write("</div>");

  /*  var menuID = "addItemMenu" + $('#tAddItemPane').find('.add-item-select').size().toString();

    //nm_AddNewMenuHead("", menuID, "Select To Access Add Item Menu", "Add Item");


    nm_AddNewMenuItemWithUrl('ai_1', '/img/appearances/appearance.gif', 'Appearance', '/otcs/cs.exe?func=ll&objType=480&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_2', '/img/otsapxecm/otsapwksp_workspace_b8.png', 'Business Workspace', '/otcs/cs.exe?func=ll&objType=848&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_3', '/img/webattribute/16category.gif', 'Category', '/otcs/cs.exe?func=ll&objType=131&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_4', '/img/collections/collection.gif', 'Collection', '/otcs/cs.exe?func=ll&objType=298&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_5', '/img/webdoc/cd.gif', 'Compound Document', '/otcs/cs.exe?func=ll&objType=136&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_6', '/img/webdoc/customview.gif', 'Custom View', '/otcs/cs.exe?func=ll&objType=146&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_7', '/img/discussion/16discussion.gif', 'Discussion', '/otcs/cs.exe?func=ll&objType=215&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_8', '/img/webdoc/doc.gif', 'Document', '/otcs/cs.exe?func=ll&objType=144&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_9', '/img/otemail/emailfolder.gif', 'E-mail Folder', '/otcs/cs.exe?func=ll&objType=751&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_10', '/img/webdoc/folder.gif', 'Folder', '/otcs/cs.exe?func=ll&objType=0&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_11', '/img/form/16_form.gif', 'Form', '/otcs/cs.exe?func=ll&objType=223&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_12', '/img/form/16_template.gif', 'Form Template', '/otcs/cs.exe?func=ll&objType=230&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_13', '/img/cmbase/folder.gif', 'Hierarchical Storage Folder', '/otcs/cs.exe?func=ll&objType=31109&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_14', '/img/report/16report.gif', 'LiveReport', '/otcs/cs.exe?func=ll&objType=299&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_15', '/img/physicalobjects/physical_item.gif', 'Physical Item', '/otcs/cs.exe?func=ll&objType=411&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_16', '/img/polling/16poll.gif', 'Poll', '/otcs/cs.exe?func=ll&objType=218&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_17', '/img/webprospector/prospector.gif', 'Prospector', '/otcs/cs.exe?func=ll&objType=384&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_18', '/img/webdoc/16x_alias.gif', 'Shortcut', '/otcs/cs.exe?func=ll&objType=1&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_19', '/img/task/16tasklist.gif', 'Task List', '/otcs/cs.exe?func=ll&objType=204&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_20', '/img/webdoc/apptext.gif', 'Text Document', '/otcs/cs.exe?func=ll&objType=145&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_21', '/img/webdoc/url.gif', 'URL', '/otcs/cs.exe?func=ll&objType=140&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_22', '/img/webdoc/virtual_folder.png', 'Virtual Folder', '/otcs/cs.exe?func=ll&objType=899&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_23', '/img/webreports/webreports.gif', 'WebReport', '/otcs/cs.exe?func=ll&objType=30303&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_24', '/img/webwork/16workfmp.gif', 'Workflow Map', '/otcs/cs.exe?func=ll&objType=128&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_25', '/img/webwork/16_wfstatus.gif', 'Workflow Status', '/otcs/cs.exe?func=ll&objType=190&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
    nm_AddNewMenuItemWithUrl('ai_26', '/img/xmlsearch/dtd.gif', 'XML DTD', '/otcs/cs.exe?func=ll&objType=335&objAction=create&parentId=3350022&nextURL=%2Fotcs%2Fcs%2Eexe%3Ffunc%3Dzsabrisutils%2EBrowseView%26objID%3D3350022');
   */
    nms_AddNewMenuEnd(myDoc);

/*
    $('#tAddItemPane').find('.toolbar-additem-button').mouseover(function () {
        $(this).addClass("toolbar-additem-button-mo");
    }).mouseout(function () {
        $(this).removeClass("toolbar-additem-button-mo");
    });
*/


}

function fillDate(id) {

    var value = $("[name=" + id + "]").attr("value");
    if (value == undefined || value.length == 0 || value == " ") return;
    var date = parseBackendDate(value);
    var dateStr = dateStringFromDate(date, "/");
    $("#_datepicker_" + id).attr("value", dateStr);
}

function parseBackendDate(sourceDate) {
    var components = sourceDate.split(":");
    var dateSend = new Date(components[0].replace("D/", ""));
    var hoursSend = components[1];
    var minutesSend = components[2];
    var secondsSend = components[3];

    dateSend.setHours(hoursSend, minutesSend, secondsSend);
    return dateSend;
}

function dateStringFromDate(date, separator) {
    var dd = date.getDate();
    var mm = date.getMonth() + 1;
    var yyyy = date.getFullYear();

    if (dd < 10) dd = "0" + dd;
    if (mm < 10) mm = "0" + mm;


    return dd + separator + mm + separator + yyyy;
}

function timeStringFromDate(date, separator) {
    var hours = date.getHours() < 10 ? ("0" + date.getHours()) : date.getHours();
    var minutes = date.getMinutes() < 10 ? ("0" + date.getMinutes()) : date.getMinutes();
    var seconds = date.getSeconds() < 10 ? ("0" + date.getSeconds()) : date.getSeconds();

    return hours + separator + minutes + separator + seconds;
}


function initDatepicker(elem) {
    $(elem).datepicker({
        numberOfMonths: 2,
        showButtonPanel: false,
        showAnim: "show",
        dateFormat: "dd/mm/yy",
        showOn: "both",
        changeMonth: true,
        changeYear: true,
        buttonImage: "/img/datepicker.gif",
        buttonImageOnly: true,
        autoSize: true,
        minDate: new Date(0, 0, 1),
        maxDate: new Date(9999, 11, 31),
        monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        dayNamesShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        prevText: "Назад",
        nextText: "Вперед",
        firstDay: 1,
        disabled: true,
		showCurrentAtPos: 1	
    });
}


function createTasksForContacts() {
    /*
"Из ""Кому"" или ""Копия"" получить все ФИО, среди них выбрать те: 
- которые есть в выпадающем списке Исполнителей поручений, 
- среди выбранных оставить те, которых нет полях рассмотрение РН и Руководитель Проекта. 
Для каждого выбранного сотрудника создать поручение с предзаполненным Исполнителем (сотрудник из «Кому» или «Копия»), сроком и целью поручения. "
    */


    var rnValues = $("#_1_1_82_1").val().split(",").map(function (name) {
        return name.trim()
    }).filter(function (name) {
        return name != undefined && name.length > 0;
    });

    var pmValues = $("[name=_1_1_7_1_Name]").val().split(",").map(function (name) {
        return name.trim()
    }).filter(function (name) {
        return name != undefined && name.length > 0;
    });

    var alreadyCreatedValues = $(".task-performer .valueTag").map(function () {
        return this.value;
    }).get().filter(function (val) {
        return val.length > 0
    });

    LookupManager.get(LookupManager.FUNCTIONS.ZEDM_TASKSPERFORMER, [projectParams.ProjectCode], function (rows) {
        var avalibleValues = rows.map(function (row) { return row.FIO; });


        $(".contacts-row").each(function (i, contactsRow) {

            var $contactsRow = $(contactsRow);
            var name = $contactsRow.find("li:eq(1) input").val().trim();

            console.log("Find name", name);

            var isNameInRn = rnValues.find(function (val) {
                return val.toLowerCase() == name.toLowerCase();
            }) != undefined;

            console.log("isNameInRn", isNameInRn);


            var isNameInPm = pmValues.find(function (val) {
                return val.toLowerCase() == name.toLowerCase();
            }) != undefined;

            console.log("isNameInPm", isNameInPm);

            var isNameCareated = alreadyCreatedValues.find(function (val) {
                return val.toLowerCase() == name.toLowerCase();
            }) != undefined;

            console.log("isNameCareated", isNameCareated);


            var isNameAvalible = avalibleValues.find(function (val) {
                return val.toLowerCase() == name.toLowerCase();
            }) != undefined;

            console.log("isNameAvalible", isNameAvalible);

            if (!isNameInRn && !isNameInPm && !isNameCareated && isNameAvalible) {
                addTaskWithPerformer(name);
            }

        });

    });







}

/**
 * Function add new task with filled performer
 * @param {string} performerName The name of new performer
 */
function addTaskWithPerformer(performerName) {

    console.log("Create task for performer", performerName);

    AddRowTask('http://pmapp67/otcs/cs.exe', 'rowselecttasks', 'WRFormTask', '#Tasks', headerTasks, fieldsTasks, 1, function () {

        setTimeout(function () {

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_TASKSPERFORMER, [projectParams.ProjectCode], function (rows) {
                var row = rows.find(function (row) { return row.FIO == performerName; });
                if (row != undefined) {

                    var $taskPerformerWrapper = $(".task-wrapper:last .task-performer");
                    $taskPerformerWrapper.find(".valueTag:first").attr("value", row.FIO);
                    $taskPerformerWrapper.find("[name-value=ID]").attr("value", row.ID);
                    $taskPerformerWrapper.find("[name-value=FIO]").attr("value", row.FIO);

                }
            });


        }, 500);

    });

}

function dirNameUpdated() {

    console.log("dirNameUpdated");

    $("#_1_1_61_1").attr("value", $(".DirName").length);

    $("#GetPM").prop('checked', $(".DirName").length != 1);
    markDirty($("#GetPM").get());

    if ($(".DirName").length != 1) {
        $("#_1_1_82_1").attr("value", "");
        $("#_1_1_82_1").attr("title", "");
        $("[name=_1_1_82_1_Name]").attr("value", "");
        $("[name=_1_1_82_1_ID]").attr("value", "");
    } else {
        loadOptions("DM", [$(".DirName:first select").val()]);
    }
}

/**
 * Function fixs bottom menu when user scrolls
 */
function fixBottomMenuOnScroll() {
    var scrollTop = $(this).scrollTop();
    var bottomMenuWrapper = $(".bottom-menu-wrapper:first");
    var bottomPosition = bottomMenuWrapper.offset().top;
    var screenHeight = $(this).height();
    var diff = bottomPosition - screenHeight + bottomMenuWrapper.height();
    var isFixed = bottomMenuWrapper.hasClass("fixed");

    if ((diff < scrollTop) && isFixed) bottomMenuWrapper.removeClass("fixed");
    else if ((diff >= scrollTop) && !isFixed) bottomMenuWrapper.addClass("fixed");
}
/*
function resizeIFrameToFitContent( iFrame ) {
    console.log("FRAME", iFrame);
    iFrame.width  = iFrame.contentWindow.document.body.scrollWidth;
    iFrame.height = iFrame.contentWindow.document.body.scrollHeight;
}

window.addEventListener('DOMContentLoaded', function(e) {


} );*/

$(function () {
   // loadFolders()

    fillDate("_1_1_97_1");


    /*
В разделе инженерных документов изменить заполнение ISNEWREVISION - если ревизия новая то в name _1_1_66_*_171_* передавать 1, иначе передавать 0. (ранее передавалось в _1_1_66_1_138) 

    */
    setInterval(function () {
        $(".document-item").each(function (i, e) {
            var revision = $($(e).find("[fieldname=REVISION]")).val();
            var revisionInternal = $($(e).find("[fieldname=REVISIONINTERNAL]")).val();

            var lastRevision = $($(e).find("[fieldname=LASTREVISIONCUR]")).val();
            var lastRevisionInternal = $($(e).find("[fieldname=LASTREVISIONINTERNALCUR]")).val();

            var newRevField = $($(e).find("[fieldname=ISNEWREVISION]"));

            if (revision == lastRevision && revisionInternal == lastRevisionInternal) {
                newRevField.attr("value", "0");
                //newRevField.attr("name", "");
            } else {
                newRevField.attr("value", "1");
                //newRevField.attr("name", newRevField.attr("id"));
            }

        })
    }, 1000)


    var typeValue = $("#_1_1_55_1").attr('value');
    var subtypeValue = $("#_1_1_52_1").attr('value');
    var datePickerValue = $('#_datepicker__1_1_93_1').attr('value');

    var typeIsFilled = typeof typeValue !== typeof undefined && typeValue !== false && typeValue.length > 0;
    var subTypeIsFilled = typeof subtypeValue !== typeof undefined && subtypeValue !== false && subtypeValue.length > 0;
    var datePickerIsFilled = typeof datePickerValue !== typeof undefined && datePickerValue !== false && datePickerValue.length > 0;
    var checkboxIsChecked = $("#_1_1_143_1").is(":checked");
    if (subTypeIsFilled && typeIsFilled && !datePickerIsFilled && checkboxIsChecked) {

        fillDate("_1_1_93_1");
        if ($("#_datepicker__1_1_93_1").val().length == 0) {
            loadOptions("_1_1_93_1", [typeValue, subtypeValue]);
        }

    }


    $(window).scroll(fixBottomMenuOnScroll);
    setTimeout(function () { fixBottomMenuOnScroll.bind(window); }, 0);

    $("HTML, BODY").animate({ scrollTop: 0 }, 0);
    $(window).load(function () {
        $("HTML, BODY").animate({ scrollTop: 0 }, 0);
        createTasksForContacts();
        //checkEngDocs();
    });




});


function checkTypeAndSubtypeLoaded() {
    var typeValue = $("#_1_1_55_1").attr("value");
    var subtypeValue = $("#_1_1_52_1").attr("value");

    if (typeValue != undefined && typeValue.length > 0 && subtypeValue != undefined && subtypeValue.length) {
        loadOptions("_1_1_97_1", [typeValue, subtypeValue]);
    } else {

        var jqElement = $("[name=_1_1_97_1]")
        if (jqElement.val() != undefined && jqElement.val().length > 0) {
            fillDate("_1_1_97_1");
            $("#_datepicker__1_1_97_1").datepicker("enable");
        } else {
            $("#_datepicker__1_1_97_1").datepicker("enable");
            $("#_datepicker__1_1_97_1").attr("value", "");
        }

    }
}


var firstLoad = true;
function resizeIframe(obj) {

    //Autoheight
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';

    //REload if error
    if(obj.contentWindow.document.body.innerHTML.indexOf("Content Server Error") != -1){
        obj.contentWindow.location.href = $("#iframeBrowseView").attr("src");//reload(true);
    }

   // if (firstLoad) {
        var cssLink = document.createElement("link");
        cssLink.href = "/img/zsabrissupd/css/iframe.css"; 
        cssLink.rel = "stylesheet"; 
        cssLink.type = "text/css";

        var scriptLink = document.createElement("script");
        scriptLink.src = "/img/zsabrissupd/js/iframe.js"; 
        scriptLink.type = "text/javascript";

        var currentURLInput = document.createElement("input");
        currentURLInput.type = "hidden";
        currentURLInput.id = "parentURL";
        currentURLInput.value = encodeURIComponent(window.location.pathname).replace(".", "%2E");//href.replace(window.location.origin, "");
//
        obj.contentWindow.document.body.appendChild(currentURLInput);
        obj.contentWindow.document.head.appendChild(scriptLink);
        obj.contentWindow.document.head.appendChild(cssLink);
   // }
    

  /*  if (!firstLoad) {
        //alert(obj.contentWindow.location);
        console.log(obj.contentWindow.location);

        var iframe = $("#iframeBrowseView");

        if (obj.contentWindow.location == "http://pmapp67/otcs/cs.exe") {
            iframe.css({ position: "fixed", top: 0, left: 0, right: 0, bottom: 0, width: "100vw", height: "100vh", "z-index": 99999 });
        } else {
            // iframe.removeAttr( 'style' );
            // obj.contentWindow.location.reload(true);
        }


        //    var newWindow = window.open(obj.contentWindow.location, 'Dynamic Popup', 'scrollbars=auto, resizable=no, location=no, status=no');
        //   console.log(iframe[0].outerHTML)

        //   var iframeDocument = iframe.contentDocument || iframe.contentWindow.document;

        //  newWindow.document.write(iframeDocument);
        // newWindow.document.close();
        // iframe[0].outerHTML = ''; // to remove iframe in page.

    }
*/
    firstLoad = false;
}

function loadOptions(name, params) {

    var jqElement = $('[name=' + name + ']');
    switch (name) {
        case '_1_1_55_1': //TYPE

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_TYPE, [], function (rows) {
                var currentVal = jqElement.val();
                jqElement.empty();

                var selecedExist = false;
                rows.forEach(function (row) {
                    var value = row.TYPE;
                    if (value == currentVal) {
                        selecedExist = true;
                        jqElement.append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                    } else
                        jqElement.append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
                });

                if (!selecedExist) {
                    jqElement.prepend("<OPTION SELECTED DISABLED VALUE> Выберите тип сообщения </OPTION>");
                } else {
                    jqElement.prepend("<OPTION DISABLED VALUE> Выберите тип сообщения </OPTION>");
                }

                jqElement.removeClass("loading-state");
                checkFieldMulti(jqElement, 'TYPE', rows)
                checkTypeAndSubtypeLoaded();
            });


            break;
        case '_1_1_52_1': //SUBTYPE

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_SUBTYPE, [projectParams.ProjectCode, params[0]], function (rows) {

                var currentVal = jqElement.val();
                jqElement.empty();
                var selecedExist = false;
                rows.forEach(function (row) {
                    var value = row.SUB_TYPE;
                    if (value == currentVal || rows.length == 1) {
                        selecedExist = true;
                        jqElement.append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                    } else
                        jqElement.append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
                });

                if (!selecedExist) {
                    jqElement.prepend("<OPTION SELECTED DISABLED VALUE> Выберите подтип сообщения </OPTION>");
                } else {
                    jqElement.prepend("<OPTION DISABLED VALUE> Выберите подтип сообщения </OPTION>");
                }

                jqElement.removeClass("loading-state");
                checkFieldMulti(jqElement, 'SUB_TYPE', rows)
                checkTypeAndSubtypeLoaded();

            });
            break;

        case 'DirName':

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_DIRNAME, [projectParams.ProjectCode], function (rows) {
                var dirName = $('.DirName [title=DirNum]');
                for (var i = 0; i < dirName.length; i++) {
                    var currentVal = $(dirName[i]).val();
                    $(dirName[i]).empty();
                    var selecedExist = false;

                    rows.filter(function (e) {
                        return (e.DIRECTIONS != undefined && e.DIRECTIONS.length > 0);
                    }).forEach(function (e) {
                        var value = e.DIRECTIONS;

                        if (value == currentVal) {
                            selecedExist = true;
                            $(dirName[i]).append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                        } else
                            $(dirName[i]).append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");

                    });

                    if (!selecedExist) {
                        if (rows.length == 1) {
                            $(dirName[i]).prepend("<OPTION DISABLED VALUE> Выберите направление </OPTION>");
                            $(dirName[i]).find("[value='" + rows[0].DIRECTIONS + "']").prop("SELECTED", true);
                        } else {
                            $(dirName[i]).prepend("<OPTION SELECTED DISABLED VALUE> Выберите направление </OPTION>");
                        }
                    } else {
                        $(dirName[i]).prepend("<OPTION DISABLED VALUE> Выберите направление </OPTION>");
                    }

                    $(dirName[i]).removeClass("loading-state");
                    checkFieldMulti($(dirName[i]), 'DIRECTIONS', rows);
                }
            });

            break;
        case '_1_1_93_1': //DateAnswer

            $("#_datepicker__1_1_93_1").attr("value", "Загрузка");

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_DUEDATEANSWER, [projectParams.ProjectCode, projectParams.fromContractorCode, params[0], params[1]], function (rows) {

                var answerDuration = parseInt(rows[0].ANSWERDURATION);
                var dateSendStr = $("#_1_1_121_1").attr("value");


                if (dateSendStr != undefined) {
                    var date = parseBackendDate(dateSendStr);
                    var dateStr = dateStringFromDate(date, "-");
                    var timeStr = timeStringFromDate(date, ":");

                    var overload = {
                        'func': 'ZCALENDAR.add_days',
                        'startDate': dateStr,
                        'daysCount': answerDuration,
                        'zcontracts_calendar_id': 'zcalendar',
                        'time': timeStr
                    };


                    $.getJSON('/otcs/cs.exe', overload, function (data) {

                        if (data.status == "success") {
                            var rightStr = data.ExpireDate.substring(0, data.ExpireDate.length - 8) + data.time;

                            var date = new Date(rightStr);
                            var dateStr = "D/" + date.getFullYear() + "/" + (date.getMonth() + 1) + "/" + date.getDate() + ":" + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
                            var notFilledNames = $(".task-datepicker").map(function () {
                                return this.id.replace("_datepicker_", "");
                            }).get().filter(function (name) {
                                return $("#_datepicker_" + name).val().length = 0
                            });

                            notFilledNames.push("_1_1_93_1");

                            notFilledNames.forEach(function (name) {
                                $("#_datepicker_" + name).attr("value", dateStringFromDate(date, "/"));
                                $("[name=" + name + "]").attr("value", dateStr);
                                $("[name=" + name + "_hour]").attr("value", date.getHours());
                                $("[name=" + name + "_minute]").attr("value", date.getMinutes());
                                $("[name=" + name + "_second]").attr("value", date.getSeconds());
                            })
                        } else {
                            $("#_datepicker__1_1_93_1").attr("value", "");
                        }
                        $("#_datepicker__1_1_93_1").datepicker("enable");


                    });
                } else {
                    $("#_datepicker__1_1_93_1").attr("value", "");
                    $("#_datepicker__1_1_93_1").datepicker("enable");
                }

            }, function (error) {
                $("#_datepicker__1_1_93_1").attr("value", "");
                $("#_datepicker__1_1_93_1").datepicker("enable");
            });


            break;

        case "_1_1_97_1": //DueDateManager

            if (jqElement.val() != undefined && jqElement.val().length > 0 && $("#_datepicker__1_1_97_1").val() != "Загрузка") {
                return
            }

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_MANAGERTASKDURATION, [projectParams.ProjectCode, projectParams.fromContractorCode, params[0], params[1]], function (rows) {

                var answerDuration = parseInt(rows[0].MANAGERTASKDURATION);
                var dateSendStr = $("#_1_1_121_1").attr("value");

                if (dateSendStr != undefined) {
                    var date = parseBackendDate(dateSendStr);
                    var dateStr = dateStringFromDate(date, "-");
                    var timeStr = timeStringFromDate(date, ":");

                    var overload = {
                        'func': 'ZCALENDAR.add_days',
                        'startDate': dateStr,
                        'daysCount': answerDuration,
                        'zcontracts_calendar_id': 'zcalendar',
                        'time': timeStr
                    };

                    $.getJSON('/otcs/cs.exe', overload, function (dassta) {

                        if (dassta.status == "success") {
                            var rightStr = dassta.ExpireDate.substring(0, dassta.ExpireDate.length - 8) + dassta.time;
                            var date = new Date(rightStr);
                            var dateStr = "D/" + date.getFullYear() + "/" + (date.getMonth() + 1) + "/" + date.getDate() + ":" + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
                            jqElement.attr("value", dateStr);
                            $("#_datepicker__1_1_97_1").attr("value", dateStringFromDate(date, "/"));
                            $("#_datepicker__1_1_97_1").datepicker("enable");
                            $("[name=_1_1_97_1_hour]").attr("value", date.getHours());
                            $("[name=_1_1_97_1_minute]").attr("value", date.getMinutes());
                            $("[name=_1_1_97_1_second]").attr("value", date.getSeconds());
                        } else {
                            $("#_datepicker__1_1_97_1").datepicker("enable");
                        }
                    })

                }
            });


            break;

        case "AttachID":

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ATTACHID, [projectParams.mailFLD, projectParams.bodyID], function (rows) {
                $(".attach-field").each(function (i, elem) {
                    $(elem).empty().append("<option value=''> Нет </option>");
                    rows.forEach(function (e) {
                        $(elem).append("<OPTION VALUE='" + e.DATAID + "' >" + e.NAME + "</OPTION>");
                    });
                });
                $(".attach-field").removeClass("loading-state");
            });

            break;
        case '_1_1_153_1': //CONTRACT

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_CONTRACT, [projectParams.ProjectCode], function (rows) {
                var filter = new FilterSelect({
                    id: '_1_1_153_1',
                    name: '_1_1_153_1',
                    data: rows,
                    showValList: 'CONTRACT_NAME'
                })
                $('#_1_1_153_1').find("input[type=text]").removeClass("loading-state");

                var currentVal = $("[name=_1_1_153_1]").val();
                if (currentVal != undefined) {
                    if (rows.length == 1) {
                        filter.setValue(rows[0].CONTRACT_NAME)
                    } else {
                        var selectedRow = rows.find(function (row) {
                            return row.CONTRACT_NUM == currentVal;
                        });



                        if (selectedRow != undefined && selectedRow.CONTRACT_NAME != undefined)
                            $('#_1_1_153_1').find("input[type=text]").attr("value", selectedRow.CONTRACT_NAME);
                    }
                }


            });


            break;

        case '_1_1_66_1_73_1': //StepEngDoc

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCSUBSTATUS, [projectParams.ProjectCode], function (rows) {
                rows.forEach(function (e) {
                    var value = e.SUBMISSION_STATUS;
                    jqElement.append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
                });
            });
            break;

            case 'RevStatusCustomer':
            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCREVSTATUS, [projectParams.ProjectCode], function (rows) {
                $("[mark=RevStatusCustomer]").each(function (i, e) {
                    var currentVal = $(e).val();
                    $(e).empty();



                    var isValueExist = data.filter(function(row){ return row.REVISION_STATUS == currentVal}).length > 0;
                    if(!isValueExist) $(e).append("<OPTION DISABLED SELECTED VALUE='' ></OPTION>");

                    rows.forEach(function (row) {
                        var value = row.REVISION_STATUS;
                        if (value == currentVal)
                            $(e).append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                        else
                            $(e).append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
                    });
                });
            });

            break;

        case 'RevStatusInternal':
            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCREVSTATUS, [projectParams.ProjectCode], function (rows) {
                $("[mark=RevStatusInternal]").each(function (i, e) {
                    var currentVal = $(e).val();
                    $(e).empty();

                    var isValueExist = data.filter(function(row){ return row.REVISION_STATUS == currentVal}).length > 0;
                    if(!isValueExist) $(e).append("<OPTION DISABLED SELECTED VALUE='' ></OPTION>");

                    rows.forEach(function (row) {
                        var value = row.REVISION_STATUS;
                        if (value == currentVal)
                            $(e).append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                        else
                            $(e).append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
                    });
                });
            });


            break;
        case 'SubStatus':

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCSUBSTATUS, [projectParams.ProjectCode], function (data) {
                $("[mark=SubStatus]").each(function (i, e) {
                    var currentVal = $(e).val();
                    $(e).empty();

                    var isValueExist = data.filter(function(row){ return row.SUBMISSION_STATUS == currentVal}).length > 0;
                    if(!isValueExist) $(e).append("<OPTION DISABLED SELECTED VALUE='' ></OPTION>");

                    data.forEach(function (row) {
                        var value = row.SUBMISSION_STATUS;
                        if (value == currentVal){
                            $(e).append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                        }else{
                            $(e).append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
                        }
                    });
                });
            });


            break;



        case "DM_INIT":
            var idField = $("[name=_1_1_82_1_ID]");
            if (idField.val() != undefined && idField.val().length > 0) {

                var fio, name;

                // Ищем группу по id
                for (var prop in DIC.ZEDM_DM) {
                    if (DIC.ZEDM_DM[prop] != undefined && DIC.ZEDM_DM[prop].length > 0 && DIC.ZEDM_DM[prop][0] != undefined && DIC.ZEDM_DM[prop][0].length > 0 && DIC.ZEDM_DM[prop][0][0] != undefined && DIC.ZEDM_DM[prop][0][0] == idField.val()) {
                        fio = DIC.ZEDM_DM[prop].map(function (row) {
                            return row[2];
                        }).join(", ");
                        name = DIC.ZEDM_DM[prop][0][1];
                        break;
                    }
                }

                // Если не нашли ищем по taskperformer
                if (fio == undefined && name == undefined) {
                    LookupManager.get(LookupManager.FUNCTIONS.ZEDM_TASKSPERFORMER, [projectParams.ProjectCode], function (rows) {
                        var row = rows.find(function (row) { return row.ID == idField.val(); });
                        if (row != undefined) {
                            $("#_1_1_82_1").attr("value", row.FIO);
                            $("#_1_1_82_1").attr("title", row.FIO);
                            $("[name=_1_1_82_1_Name]").attr("value", row.FIO);
                        }
                    });
                } else {
                    $("#_1_1_82_1").attr("value", fio);
                    $("#_1_1_82_1").attr("title", fio);
                    $("[name=_1_1_82_1_Name]").attr("value", name);
                }

            }

            break;

        case "DM":

            if ($(".DirName").length > 1) {
                $("#_1_1_82_1").attr("value", "");
                $("[name=_1_1_82_1_Name]").attr("value", "");
                $("[name=_1_1_82_1_ID]").attr("value", "");
                $("#_1_1_82_1").attr("title", "");
                break;
            }

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_DM, [params[0], projectParams.ProjectCode], function (rows) {
                console.log(rows);
                if (rows != undefined && rows.length > 0) {
                    var name = rows[0].NAME;
                    var id = rows[0].ID;
                    var fio = rows.map(function (row) {
                        return row.FIO
                    }).join(", ");
                    $("#_1_1_82_1").attr("value", fio);
                    $("#_1_1_82_1").attr("title", fio);
                    $("[name=_1_1_82_1_Name]").attr("value", name);
                    $("[name=_1_1_82_1_ID]").attr("value", id);
                } else {
                    $("#_1_1_82_1").attr("value", "");
                    $("[name=_1_1_82_1_Name]").attr("value", "");
                    $("[name=_1_1_82_1_ID]").attr("value", "");
                    $("#_1_1_82_1").attr("title", "");
                }

            }, function (error) {
                $("#_1_1_82_1").attr("value", "");
                $("[name=_1_1_82_1_Name]").attr("value", "");
                $("[name=_1_1_82_1_ID]").attr("value", "");
                $("#_1_1_82_1").attr("title", "");
            });

            break;

        case "PM":
            var isInitialLoading = params != undefined && params.length > 0 && params[0] == true;
            if (!$("[name=GetPM]").is(":checked") && !isInitialLoading) return

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_PM, [projectParams.ProjectCode], function (rows) {

                if (rows == undefined || rows.length == 0) {
                    alert("Невозможно определить Руководителя проекта");
                    $("[name=GetPM]").prop("checked", false);
                    return;
                }

                if (isInitialLoading) {

                    if (rows[0].ID == $("[name=_1_1_7_1_ID]").val()) {
                        $("[name=GetPM]").prop("checked", true);
                        var names = rows.map(function (row) {
                            return row.FIO
                        }).join(", ");
                        $("[name=_1_1_7_1_Name]").attr("value", names);
                        $("[name=_1_1_7_1_Name]").attr("title", names);
                    }

                } else {
                    $("[name=_1_1_7_1_ID]").val("");
                    $("[name=_1_1_7_1_Name]").attr("value", "");
                    var names = rows.map(function (row) {
                        return row.FIO
                    }).join(", ");
                    $("[name=_1_1_7_1_Name]").attr("value", names);
                    $("[name=_1_1_7_1_Name]").attr("title", names);

                    if (rows[0] != undefined && rows[0].ID != undefined) $("[name=_1_1_7_1_ID]").val(rows[0].ID);
                }


            }, function (error) {
                alert("Невозможно определить Руководителя проекта");
                $("[name=GetPM]").prop("checked", false);
            })

            break;


    }
}



function checkCirilic(oElement) {
    var cirilRegExp = new RegExp(/[а-яА-ЯёЁ]/ig);
    if (cirilRegExp.test(oElement.val())) {
        oElement.css('background-color', '#bc0712');
    } else {
        oElement.css('background-color', '#ffffff');
    }
}

/**
 * Refer to project manager checkbox toogled 
 * @param {jQuery} oElement Checkbox element
 */
function checkWithEngDoc(oElement) {
    if (!oElement.is(':checked')) {
        $("[name=_1_1_7_1_ID]").val("");
        $("[name=_1_1_7_1_Name]").attr("value", "");
    } else loadOptions("PM", []);
}


function stringSelect_1_1_96_1() {
    var changeInterval;
    var w;
    var url;

    url = "/otcs/cs.exe?func=otsapxecm.SelectSapObject"
    url += "&ref_id=" + projectParams.codeBO;
    url += "&formName=AddBusinessReference";
    url += "&id_bo_type=" + projectParams.codeBO;
    url += "&isWorkspace=false";
    url += "&objID=" + projectParams.dataIdForm;
    url += "&sapfield_PSPID=" + DIC.ZEDM_SAPPROJECTCODE[0];

    var windowOpts = 'height=640,width=800,left=' + ((screen.width / 2) - (400)) + ',top=' + ((screen.height / 2) - (320)) + ',resizable=yes,menubar=no,scrollbars=yes,toolbar=yes';
    var w = window.open(url, 'OSG', windowOpts);

    var newWinInit = false;
    while (newWinInit === false) {
        if (typeof w.addEventListener === 'function') {
            newWinInit = true;
        }
    }

    var PSPNRField = w.document.getElementById("sapfield_PSPID");
    if (PSPNRField != null) {
        PSPNRField.value = DIC.ZEDM_SAPPROJECTCODE[0];
    } else {
        w.addEventListener('load', function () {
            var PSPNRField = this.document.getElementById("sapfield_PSPID");
            PSPNRField.value = DIC.ZEDM_SAPPROJECTCODE[0];
        });
    }



    function changeResultAction() {
        if (w && !w.closed && w.selectSapObject) {
            w.selectSapObject = function (key, nodeID) {
                $('[name="_1_1_96_1"]').val(nodeID);
                $('[name="_1_1_127_1"]').val(key);

                w.close();
            };
            w.sc_partnerHook = true;
        }
        if (w.closed) {
            clearInterval(changeInterval);
        }
    }
    changeInterval = setInterval(changeResultAction, 100);

}


var totalErrors = [];
/**
 * Toogle documents erros table
 * @param {any} elem Show errors button element
 */
function showDocumentsErrors(elem) {

    var oElement = $(elem);
    var errorsBlock = $(".docs-errors-block:first");

    if (oElement.val() == "Показать ошибки автопроверки") {
        oElement.val("Скрыть ошибки автопроверки");
        errorsBlock.show();


    } else {
        errorsBlock.hide();
        oElement.val("Показать ошибки автопроверки");
    }


}

function generateErrorsTable(mappedData) {

    var errorsDom = $("<table border='1'></table>");

    // Header of the table
    errorsDom.append($("<tr>\
            <th>№</th>\
            <th>Вложение</th> \
            <th>№ альбома</th>\
            <th>№ листа</th>\
            <th>Ревизия</th>\
            <th>Ошибки</th>\
        </tr>"));

    mappedData.forEach(function (errors, row) {

        var errorDom = $("<tr></tr>");

        // Row number
        errorDom.append("<td>" + (row + 1) + "</td>");


        // Attach name
        var attachName = $($(".document-item")[row]).find(".attach-field:first option:selected").text();
        if (attachName != undefined) errorDom.append("<td style='text-align: left;'>" + attachName + "</td>");
        else errorDom.append("<td> - </td>");

        // Album number
        var albumName = $($(".document-item")[row]).find(".album-num:first").val();
        if (albumName != undefined) errorDom.append("<td style='text-align: left;'>" + albumName + "</td>");
        else errorDom.append("<td> - </td>");

        // List of album
        var listName = $($(".document-item")[row]).find(".list-field:first").val();
        if (listName != undefined) errorDom.append("<td>" + listName + "</td>");
        else errorDom.append("<td> - </td>");


        // Revision number
        var revName = $($(".document-item")[row]).find("[mark=Revision] [name-value=REVISION]").val();
        if (revName != undefined) errorDom.append("<td>" + revName + "</td>");
        else errorDom.append("<td> - </td>");

        // Errors messages
        var messages = errors.reduce(function (previousValue, currentValue, index, array) {
            if (index > 0) {
                return previousValue + ", " + currentValue.message;
            } else {
                return previousValue + currentValue.message;
            }
        }, "");
        $($(".document-item")[row]).find("[fieldname=ERRORTEXT]").attr("value", messages.substr(0, 253));

        errorDom.append("<td>" + messages + "</td>");

        errorsDom.append(errorDom);
    });

    return errorsDom;
}

function checkEngDocs() {

    removeEmptyDocsRows();

    var $checkDocsButton = $("#CheckEngDocButton");
    var $docsWrapper = $(".documents-wrapper:first");
    var $docsItems = $docsWrapper.find(".document-item");

    if ($docsItems.length == 0) {
        $docsWrapper.removeClass("myDisabled");
        $checkDocsButton.attr("value", "Проверка");
        return;
    }

    $(".has-errors").removeClass("has-errors");
    $("[fieldname=ERRORTEXT]").attr("value", "");


    /**
     * @type {{ $input: jQuery, message: string, row: number }[]}
     */
    var totalErrors = [];


    var checkedRowsCount = 0;
    var showErrors = function () {
        checkedRowsCount++;

        if (checkedRowsCount < $docsItems.length) return;

        console.log("Display errors", totalErrors);

        var mappedData = [];

        // Clear titles before
        $docsWrapper.find("input").each(function (i, q) {
            $(q).attr("title", "");
        });

        // Mark wrong fields (title & class)
        totalErrors.forEach(function (error) {
            if (mappedData[error.row] == undefined) mappedData[error.row] = [];
            mappedData[error.row].push(error);
            if (typeof $(error.$input.parentElement).attr('mark') == "Revision") {
                $(error.$input.parent()).parent().find('[mark=Revision] ~ input').addClass('has-errors');
            }
            error.$input.closest("div").addClass("has-errors");
            error.$input.closest("div").attr("title", error.message);
        });

        // Mark rows (left border)
        $docsItems.each(function (i, item) {
            if (mappedData[i] == undefined) $(item).addClass("green");
            else $(item).addClass("red");
        });

        $docsWrapper.removeClass("myDisabled");
        $checkDocsButton.attr("value", "Проверка");

        var errorsBlock = $(".docs-errors-block:first");
        errorsBlock.empty();

        if (totalErrors.length == 0) {
            errorsBlock.append("<center style='color: #605b5b;'>Нет ошибок, для проверки нажмите кнопку 'Проверить'</center>");
        } else {
            var errorsDom = generateErrorsTable(mappedData);
            errorsBlock.append(errorsDom);
        }




    }

    $docsItems.each(function (i, documentItem) {
        console.log("Begin " + i + " row check");

        var $documentItem = $(documentItem);

        // Clear border colors
        $documentItem.removeClass("red");
        $documentItem.removeClass("green");

        // Define inputs
        var $attachField = $documentItem.find(".attach-field:first");
        var $titleField = $documentItem.find("[fieldname=NAME]");
        var $albumField = $documentItem.find(".album-num:first");
        var $listVisibleField = $documentItem.find(".selectize-control.list-field:first");
        var $listField = $documentItem.find(".list-field:first");
        var $cdidField = $documentItem.find("[fieldname=CDID]");


        var $revField = $documentItem.find("[mark=Revision] input[type=text]");
        var $revInternalField = $documentItem.find("[mark=RevisionInternal]");
        var $subStatusField = $documentItem.find("[mark=SubStatus]");


        // Check attach
        if ($attachField.val().length <= 0) {
            totalErrors.push({
                $input: $attachField,
                message: "Необходимо выбрать вложение",
                row: i
            });
        } else if (/[\u0400-\u04FF]/.test($titleField.val())) {
            totalErrors.push({
                $input: $titleField,
                message: "Наименование не дожно содержать кириллических символов",
                row: i
            });
        }

        // Check album number
        if ($albumField.val().length <= 0) {
            totalErrors.push({
                $input: $albumField,
                message: "Введите номер альбома",
                row: i
            });
        }


        // Get list data
        var selectize = $listField.get(0).selectize;
        var listData = undefined;
        if (selectize != undefined) {
            var values = Object.values || valuesPolyfill;
            console.log("values", values(selectize.options));
            listData = values(selectize.options).find(function (datas) { return datas.CDDATAID == $cdidField.val(); });
            console.log("listData", listData);

        }


        // Check list
        if (listData == undefined || listData.CDDATAID == undefined) {
            totalErrors.push({
                $input: $listVisibleField,
                message: "Выберите верный номер листа",
                row: i
            });
            showErrors();
            return;
        }


        // Check revision
        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCSUBSTATUS, [projectParams.ProjectCode], function (rows) {

            var selectedSubStatus = rows.find(function (row) {
                return row.SUBMISSION_STATUS == $subStatusField.val()
            });

            if (selectedSubStatus == undefined) {
                totalErrors.push({
                    $input: $subStatusField,
                    message: "Не верно задан этап",
                    row: i
                });
                showErrors();
                return;
            }

            if (selectedSubStatus.NEED_REVISION != "1") {
                showErrors();
                return;
            }

            if ($revField.val() == null || $revField.val().length == 0) {
                totalErrors.push({
                    $input: $revField,
                    message: "Выберите значение № ревизии",
                    row: i
                });
                showErrors();
                return;
            }

            if (listData.LASTREVISION == null || listData.LASTREVISION.length == 0) {
                showErrors();
                return;
            }

            if ($revInternalField.val() != undefined && $revInternalField.val().length > 0) {
                // If revision internal has filled =>
                // a) new revInternal must be more last internal revision by 1
                // or
                // b) if lastRevInternal == null && new revision is next to last revision (A > B) && new revInternal == 1

                var lastRevisionNum;
                if (listData.LASTREVISION == null) lastRevisionNum = 0;
                else lastRevisionNum = listData.LASTREVISION.charCodeAt(0);


                var lastRevInternalIsNull = (listData.LASTREVISIONINTERNAL == undefined);

                var a = !lastRevInternalIsNull && (parseInt($revInternalField.val()) == (parseInt(listData.LASTREVISIONINTERNAL) + 1));

                var newRevIsNextToLast = ($revField.val().charCodeAt(0) == (lastRevisionNum + 1));
                var newRevInternalIsOne = parseInt($revInternalField.val()) == 1;
                var b = lastRevInternalIsNull && newRevIsNextToLast && newRevInternalIsOne;

                if (!a && !b) {
                    totalErrors.push({
                        $input: $revInternalField,
                        message: "Не выполенены условия для номера новой ревизии",
                        row: i
                    });
                }

                showErrors();

            } else {

                // If revision internal has not filled => revision must be equal last revison
                if ($revField.val() != listData.LASTREVISION && listData.LASTREVISION != null) {
                    totalErrors.push({
                        $input: $revInternalField,
                        message: "Заданный номер ревизии должен совпадать с номером последней ревизии - " + listData.LASTREVISION,
                        row: i
                    });
                }
                showErrors();

            }


        }, function (error) {
            totalErrors.push({
                $input: $revField,
                message: "Не удается загрузить номера этапов (Ошибка сети)",
                row: i
            });
            showErrors();
        });


    });

}

/**
 * Old function. use checkEngDoc()
 */
function engDocCheck(elemdsds) {
    checkEngDocs();
    if ($(".document-item").length == 0) {
        loadedRowCount = -999;
    }
    //setTimeout(checkEngDocs, 200)
    return;
}

function setError(sName) {
    var targetElement;

    if (typeof (sName) == 'string') {
        targetElement = $('[name=' + sName + ']');
    } else targetElement = sName;

    if (!targetElement.length) targetElement = $('[list-id="' + sName + '"] input[type=text]');
    targetElement.css('background-color', '#d89497');
}

function checkFieldMulti(sName, sNeedParam, aValues) {
    var oElement,
        sValue = null;
    if (typeof (sName) == 'string') {
        oElement = $('[name=' + sName + ']');
    } else oElement = sName;

    sValue = oElement.val();
    if (sValue) {
        var bError = !!aValues.filter(function (obj) {
            return obj[sNeedParam] == sValue;
        }).length;

        if (!bError) {
            setError(sName);
        }
    }
}


function ShowMessage(sType, sMessage, aSubMessage, cb) {
    var concatMessages = "", buttons = "", subMessages = "", buttonCancel = "<a href='#' class='button cancel'>Отмена</a>", buttonAccept = "<a href='#' class='button accept'>Выбрать</a>"

    switch (sType) {
        case 'error':
            buttons = buttonCancel;
            concatMessages += "<p style='text-align: center;font-size: 20px;'>" + sMessage + "</p>";
            if (aSubMessage && $.isArray(aSubMessage)) {
                aSubMessage.forEach(function (item, i) {
                    subMessages += "<p style='color: #bc0712;text-align: center;'>" + (i + 1) + ". " + item + "</p>";
                })
            } else {
                subMessages = '';
            }
            break;
        case 'message':
            concatMessages += "<p style='text-align: center;font-size: 20px;'>" + sMessage + "</p>";
            if (aSubMessage && $.isArray(aSubMessage)) {
                aSubMessage.forEach(function (item) {
                    subMessages += "<p style='text-align: center;'>" + (i + 1) + ". " + item + "</p>";
                })
            } else {
                subMessages = '';
            }
            break;
        default:
            buttons = buttonAccept + buttonCancel;
            concatMessages += sMessage;
            break;
    }
    var overlay = $('<div id="dialog-overlay" style="position: fixed;"></div>');
    var domModalWindow = $('<div id="dialog-box" style="position: fixed;">' +
        '<div class="dialog-content">' +
        '<div id="dialog-message">' +
        concatMessages + subMessages +
        '</div><div class="buttons-wrap">' +
        buttons +
        '</div></div>' +
        '</div>');

    $(domModalWindow.find('a')).on('click', function (event) {
        if ($.isFunction(cb) && !$(event.target).hasClass('cancel')) {
            cb();
        }
        this.domModalWindow.remove();
        this.overlay.remove();
    }.bind({
        domModalWindow: domModalWindow,
        overlay: overlay
    }));

    if (sType != 'message') {
        overlay.appendTo($('body'));
    }
    domModalWindow.appendTo($('body'));

    var maskHeight = $(document).height();
    var maskWidth = $(document).width();
    var dialogTop = (($(window).height() / 2)) - (domModalWindow.height() / 2);
    var dialogLeft = (maskWidth / 2) - (domModalWindow.width() / 2);

    if (sType == 'redirect') {
        var data = DIC.ZEDM_TASKSPERFORMER.map(function (item) {
            return {
                id: item[0],
                name: item[1]
            }
        })
        var filter = new FilterSelect({
            id: 'forDM',
            data: data,
            showValList: 'name'
        })
    }
    overlay.css({
        height: maskHeight,
        width: maskWidth
    });
    domModalWindow.css({
        top: dialogTop,
        left: dialogLeft,
        border: '1px solid #96b0bf'
    });
    if (sType != 'message') {
        overlay.show();
    } else {
        setTimeout(function () {
            this.domModalWindow.remove();
        }.bind({ domModalWindow: domModalWindow }), 1500);
    }
    domModalWindow.show();
}

function Click_AnswerNotRequired() {
    applyForm('noAnswer');
}

function Click_DeliverAdministrator() {
    applyForm('toRP');
}

function Click_DeliverPerformer() {
    applyForm('toExecution');
}
function Click_Redirect() {


    ShowMessage('redirect', '<p style="text-align: center;">Перенаправить на: </p><div filter-select="" id="forDM" style="display: flex; justify-content: center; background-color: transparent;">' +
        '<input type="text" class="valueTag " style="width: 167px;" value="" ><label></label>' +
        '<ul class="selectMenuBox" style="margin-top: 19px;"></ul>' +
        '<input type="hidden"  name-value="name" value="">' +
        '<input type="hidden"  name-value="id" value="">' +
        '</div>', null, function () {
            $('[name=_1_1_82_1_Name]').val($('#forDM [name-value=name]').val())
            $('[name=_1_1_82_1_ID]').val($('#forDM [name-value=id]').val())
            applyForm('toRN');
            toogleLoader({ show: true });

        });
}

function applyForm(sButton) {
    function afterSave(sType) {

        toogleLoader({ show: false });

        var url = window.location.search.split('&').filter(function (item) { return item.match('nexturl'); });
        if (url.length) {
            url = decodeURIComponent(url[0].replace('nexturl=', ''))
        } else url = '';

        window.location = "http://" + window.location.host + "/otcs/cs.exe?func=ll&objId=" + projectParams.dataIdForm + "&objAction=submit&nexturl=" + encodeURIComponent(url)


    }
    var hasError = false,
        aMessagesError = {},
        aForms = $(document.myForm).serializeArray(),
        aTypes = {
            noAnswer: 'Ответ не требуется',
            toRN: 'Рассмотрение',
            toRP: 'Рассмотрение РП',
            isRegistration: 'Регистрация',
            isExecution: 'Исполнение',
            isReject: 'Отклонено'
        }

    var status = $('[name=_1_1_8_1]').val();

    if (status == aTypes.isRegistration) {
        $('[name=_1_1_12_1_ID]').val(projectParams.currentUser.name);
        $('[name=_1_1_12_1_SavedName]').val(projectParams.currentUser.id);
    }


    if (sButton == 'toExecution' || sButton == 'noAnswer') {

        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1; //January is 0!
        var yyyy = today.getFullYear();

        if (dd < 10) dd = '0' + dd
        if (mm < 10) mm = '0' + mm

        $("#complectionDate").attr("value", dd + "/" + mm + "/" + yyyy);

        if ($('select#_1_1_52_1').val() == null || !$('select#_1_1_52_1').val().length) {
            LookupManager.get('ZEDM_OSG', [projectParams.ProjectCode, $('select#_1_1_52_1').val()], function (rows) {
                if (rows.length) {
                    ShowMessage('error', "Заполните поле ОСГ!");
                }
            }.bind(this));
            return;
        } else {
            if (sButton == 'toExecution') {
                $('[name=_1_1_8_1]').val(aTypes.isExecution);
            } else if (sButton == 'noAnswer') {
                $('[name=_1_1_8_1]').val(aTypes.noAnswer);
            }

        }

    } else {

        if (sButton == 'toRP') {
            var oGetPm = document.getElementById('GetPM');
            if (oGetPm.checked) {
                $('[name=_1_1_8_1]').val(aTypes.toRP);
            } else {
                $('[name=_1_1_8_1]').val(aTypes.toRN);
            }
        } else if (sButton == 'toRN') {
            $('[name=_1_1_8_1]').val(aTypes.toRN);
        }

    }

    if (window.location.search.match('workid')) {



        CheckSysNumber(function (formsss) {

            removeEmptyDocsRows();
            makeRevNone()
            saveLinksFix()
            fixCheckboxValues()
            removeEmptyFields()


            formsss.submit();

        }, document.myForm, true, afterSave.bind(this, status));
    } else {
        CheckSysNumber(Click_Save, document.myForm, true, afterSave.bind(this, status));
    }

}

function updateData(selectors, fields) {

    var setData = $('div [' + selectors + ']');

    var getData = new Array(setData.length);

    for (i_m = 0; i_m < setData.length; i_m++) {

        var elsData = $(setData[i_m]).find('[fieldName]');
        var els = new Array(fields.length);
        for (i_c = 0; i_c < elsData.length; i_c++) {
            var fieldId = fields.indexOf($(elsData[i_c]).attr('fieldName'));
            if (fieldId >= 0) {
                if (els[fieldId] != null && els[fieldId].length > 0) {
                    els[fieldId] += '; ';
                }
                else {
                    els[fieldId] = '';
                }
                els[fieldId] += $(elsData[i_c]).val();
            }
        }

        getData[+$(setData[i_m]).attr(selectors) - 1] = '"' + els.join('","') + '"';

    }

    return getData;
}

function DelRow(url, selectors, nickNameWR, updateEl, header, fields, delID) {

    var getData = updateData(selectors, fields);
    getData.splice(delID - 1, 1);
    var recArray;
    if (getData.length != 0) {
        recArray = 'V{' + header + '<' + getData.join('><') + '>}';
    } else {
        recArray = 'V{' + header + '}';
    }
    UpdateDataAAAA(url, nickNameWR, updateEl, recArray);
}

function AddRow(url, selectors, nickNameWR, updateEl, header, fields, callback) {

    var add = function(){
        var getData = updateData(selectors, fields);
        var els = new Array(fields.length);
        getData.push('"' + els.join('","') + '"');
        var recArray = 'V{' + header + '<' + getData.join('><') + '>}';
        UpdateDataAAAA(url, nickNameWR, updateEl, recArray, callback);
    }

    if(nickNameWR=="WREngDoc"){
        renameAttaches(add)
    }else{
        add();
    }
    
}

function AddRowTask(url, selectors, nickNameWR, updateEl, header, fields, typeRow, callback) {
    var getData = updateData(selectors, fields);
    var els = new Array(fields.length);
    els[fields.indexOf('TTYPE')] = typeRow;
    getData.push('"' + els.join('","') + '"');
    var recArray = 'V{' + header + '<' + getData.join('><') + '>}';

    var callbackF = function () {
        UpdateTaskDataCallback();
        if (callback != undefined) callback();
    }

    UpdateDataAAAA(url, nickNameWR, updateEl, recArray, callbackF);
}

function UpdateTaskDataCallback() {
    $(".task-type-selector-hidden").each(function (index, hiddenSelector) {
        var id = $(hiddenSelector).attr("name");
        var newSelect = $('<select></select>').attr({
            name: id,
            class: "selectMenu task-type-selector loading-state"
        });

        $(hiddenSelector).replaceWith($(newSelect));
        //loadOptions('tasktype', [])
    });
}

function UpdateDataAAAA(url, nickNameWR, updateEl, recArray, callback) {

    $.post(url + '/open/' + nickNameWR, {
        DSFILETYPE: 'oscript',
        DSREQUESTDATA: recArray,
        DSHEADINGSINC: 'true'
    }, function (data) {
        $(updateEl).empty().append(data);

        if (updateEl == "#Tasks") {
            reloadTasks();
        } else if (updateEl == "#Recipients2") {
            reloadDataInDocs($(updateEl));
        }

        if (callback) {
            callback();
        }
    });
}


Selectize.define('hidden_textfield', function (options) {
    var self = this;
    this.showInput = function () {
        this.$control.css({ cursor: 'pointer' });
        this.$control_input.css({ opacity: 0, position: 'relative', left: self.rtl ? 10000 : -10000 });
        this.isInputHidden = false;
    };

    this.setup_original = this.setup;

    this.setup = function () {
        self.setup_original();
        this.$control_input.prop("disabled", "disabled");
    }
});

window.loadedRowCount = 0;

function reloadDataInDocs(docsEl) {




    var configAttachField = function (e) {
        var attachSelector = $(e).find(".attach-field:first");
        var nameField = $(e).find("[fieldname=NAME]");


        if (nameField.val() == undefined || nameField.val().length == 0 || nameField.val() == "?") {
            nameField.val("Сначала выберите вложение");
            nameField.attr("readonly", "true");
        }

        var valStr;
        if ($(attachSelector).val() != undefined && $(attachSelector).val().length > 0 && $(attachSelector).val() != "?") {
            valStr = '<select><option selected value="' + $(attachSelector).val() + '"> Загрузка </option></select>';
        } else {
            valStr = '<select><option selected value=""> Нет </option></select>';
        }
        var id = $(attachSelector).attr("ID");
        var newSelect = $(valStr).attr({
            ID: id,
            name: id,
            class: "selectMenu attach-field loading-state",
            onchange: "markDirty(this);",
            fieldname: "ATTACHID"
        });
        $(attachSelector).replaceWith($(newSelect));


        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ATTACHID, [projectParams.mailFLD, projectParams.bodyID], function (rows) {

            var currentVal = $(attachSelector).val();

            $(newSelect).empty().append("<option value=''> Нет </option>");
            rows.forEach(function (row) {

                if (row.DATAID == currentVal) {
                    selectedVal = row;
                    $(newSelect).append("<OPTION SELECTED VALUE='" + row.DATAID + "' >" + row.NAME + "</OPTION>");
                    if(!nameField.val() || !nameField.val().length){
                        nameField.val(row.NAME);
                    }
                    nameField.removeAttr("readonly");
                   // 
                   // 
                } else {
                    $(newSelect).append("<OPTION VALUE='" + row.DATAID + "' >" + row.NAME + "</OPTION>");
                }

            });


            $(newSelect).removeClass("loading-state");
        });
    }

    var configListField = function (e, lists) {


        console.log("configListField", lists);
        var listEl = $(e).find(".list-field:first");

        var initValue = listEl.val();


        var $sel = listEl.selectize({
            options: lists,
            maxOptions: 50,
            create: false,
            readOnly: true,
            plugins: ['hidden_textfield'],
            allowEmptyOption: false,
            items: [listEl.val()],
            valueField: 'LIST',
            labelField: 'LIST',
            searchField: 'LIST',
            onDelete: function () { return false },
            onInitialize: function () {

                if (initValue != listEl.val()) {
                    this.setTextboxValue(initValue);
                    $(e).find(".list-field").addClass("errored");
                }
                loadedRowCount++;
                if (loadedRowCount == $(".document-item").length) {
                    loadedRowCount = -999;
                    console.log("DOCS LOADED");
                    checkEngDocs();
                }



            },
            onFocus: function () {
                if ($(e).find(".list-field.errored").length > 0) {
                    $(e).find(".list-field.errored").removeClass("errored");
                    this.setTextboxValue("");
                }
            },
            onChange: function (newVal) {

                if (newVal != undefined) {
                    var isInitChange = newVal.indexOf("-initChange") >= 0;
                    newVal = newVal.replace("-initChange", "");


                    console.log("isInitChange", isInitChange);
                    console.log("List field changed to", newVal);

                    $(e).find(".list-field.errored").removeClass("errored");

                    var $wrapper = this.$input.closest(".document-item");

                    if (!isInitChange) {
                        $wrapper.find("[mark=Revision] .valueTag").attr("value", "");
                        $wrapper.find("[mark=Revision] [name-value=REVISION]").attr("value", "");
                        $wrapper.find("[mark=RevisionInternal]").attr("value", "");
                    }
                    $wrapper.find("[fieldname=DIR]").attr("value", "");

                    $wrapper.find("[mark=LastRevision]").attr("value", "");
                    $wrapper.find("[mark=LastRevisionInternal]").attr("value", "");
                    $wrapper.find("[fieldname=CDID]").attr("value", "");
                    $wrapper.find("[fieldname=MDLPERFORMER]").attr("value", "");
                    $wrapper.find("[fieldname=MDLPERFORMERFIO]").attr("value", "");

                    $wrapper.find("[mark=LastRevStatusInternal]").attr("value", "");
                    $wrapper.find("[mark=LastRevStatusCustomer]").attr("value", "");
                    $wrapper.find("[mark=LastSubStatus]").attr("value", "");

                    var values = Object.values || valuesPolyfill;

                    var changedList = values(this.options).find(function (al) {
                        if (newVal.length == 0) {
                            return al.LIST == newVal || al.LIST == null;
                        } else {
                            return al.LIST == newVal
                        }
                    });
                }

                console.log("changedList", changedList);


                if (changedList != undefined) {



                    if (!isInitChange) {
                        $wrapper.find("[mark=Revision] .valueTag").attr("value", changedList.LASTREVISION);
                        $wrapper.find("[mark=Revision] [name-value=REVISION]").attr("value", changedList.LASTREVISION);
                        $wrapper.find("[mark=RevisionInternal]").attr("value", changedList.LASTREVISIONINTERNAL);

                        if (changedList.REVSTATUSCUSTOMER != undefined) $wrapper.find("[mark=RevStatusCustomer]").val(changedList.REVSTATUSCUSTOMER);
                        if (changedList.REVSTATUSINTERNAL != undefined) $wrapper.find("[mark=RevStatusInternal]").val(changedList.REVSTATUSINTERNAL);
                        if (changedList.SUBSTATUS != undefined) $wrapper.find("[mark=SubStatus]").val(changedList.SUBSTATUS);

                        //$wrapper.find("[fieldname=DIR]").attr("value", changedList.DIR);

                    }
                    console.log(changedList);
                    $wrapper.find("[fieldname=DIR]").attr("value", changedList.DIR);

                    $wrapper.find("[mark=LastRevision]").attr("value", changedList.LASTREVISION);
                    $wrapper.find("[mark=LastRevisionInternal]").attr("value", changedList.LASTREVISIONINTERNAL);
                    $wrapper.find("[fieldname=CDID]").attr("value", changedList.CDDATAID);

                    if (changedList.PERFORMER != undefined) {
                        $wrapper.find("[fieldname=MDLPERFORMER]").attr("value", changedList.PERFORMER.ID);
                        $wrapper.find("[fieldname=MDLPERFORMERFIO]").attr("value", changedList.PERFORMER.NAME);
                    }

                    $wrapper.find("[mark=LastRevStatusInternal]").attr("value", changedList.REVSTATUSINTERNAL);;
                    $wrapper.find("[mark=LastRevStatusCustomer]").attr("value", changedList.REVSTATUSCUSTOMER);
                    $wrapper.find("[mark=LastSubStatus]").attr("value", changedList.SUBSTATUS);

                }

                if (!isInitChange) {
                    reloadTaskDocs();
                }
            }

        });

        return $sel;

    }

    var configAlbumField = function (e, albums) {


        var albumEl = $(e).find(".album-num:first");

        var initValue = albumEl.val();
        var $listSelect;


        var sel = albumEl.selectize({
            options: albums,
            maxOptions: 10,
            create: false,
            allowEmptyOption: false,
            placeholder: "Выберите альбом",
            items: [albumEl.val()],
            valueField: 'CUSTNUM',
            labelField: 'CUSTNUM',
            searchField: 'CUSTNUM',
            onInitialize: function () {

                if (initValue != albumEl.val()) {
                    this.setTextboxValue(initValue);
                    $(e).find(".album-num").addClass("errored");
                }

                var changedAlbum = albums.find(function (al) { return al.CUSTNUM == initValue });
                $listSelect = configListField(e, (changedAlbum == undefined) ? null : changedAlbum.LISTS);
                $listSelect[0].selectize.trigger("change", $(e).find("[fieldname=LIST]").val() + "-initChange");


                console.log("Init album", changedAlbum)

                var $wrapper = this.$input.closest(".document-item");
                if (changedAlbum != undefined) {
                    $wrapper.find("[fieldname=FLDID]").attr("value", changedAlbum.FLDDATAID);
                    if (changedAlbum.FLDDATAID != null && changedAlbum.FLDDATAID > 0) $wrapper.find(".add-filter-button:first").addClass("known");
                    else $wrapper.find(".add-filter-button:first").removeClass("known");
                } else {
                    $wrapper.find("[fieldname=FLDID]").attr("value", "");
                    $wrapper.find(".add-filter-button:first").removeClass("known");

                }

                var listEl = $(e).find(".list-field")
                if (changedAlbum == undefined || changedAlbum.LISTS.length == 0 || changedAlbum.LISTS.length == 1) {
                    listEl.addClass("myDisabled");
                } else {
                    listEl.removeClass("myDisabled");
                }
            },
            onFocus: function () {
                if ($(e).find(".album-num.errored").length > 0) {
                    $(e).find(".album-num.errored").removeClass("errored");
                    this.setTextboxValue("");
                }
            },
            onChange: function (newVal) {
                var control = $listSelect[0].selectize;
                control.setTextboxValue("");

                var listEl = $(e).find(".list-field")

                var changedAlbum = albums.find(function (al) { return al.CUSTNUM == newVal });
                var $wrapper = this.$input.closest(".document-item");


                if (changedAlbum == undefined || changedAlbum.FLDDATAID == null || changedAlbum.FLDDATAID == 0) {
                    $wrapper.find(".add-filter-button:first").removeClass("known");
                    $wrapper.find("[fieldname=FLDID]").attr("value", "");
                } else {
                    $wrapper.find("[fieldname=FLDID]").attr("value", changedAlbum.FLDDATAID);
                    $wrapper.find(".add-filter-button:first").addClass("known");
                }

                console.log("Album has been changed", changedAlbum);
                control.clearOptions();

                if (changedAlbum == undefined || changedAlbum.LISTS.length == 0) {
                    listEl.addClass("myDisabled");
                    control.clear();
                    control.trigger("change", null);

                } else {
                    listEl.removeClass("myDisabled");
                    changedAlbum.LISTS.forEach(function (list) { control.addOption(list); });

                    if (changedAlbum.LISTS.length == 1) {
                        control.setValue(changedAlbum.LISTS[0].LIST, false);
                    } else {
                        control.clear();
                        control.trigger("change", null);
                    }

                    if (changedAlbum.LISTS.length == 1) {
                        listEl.addClass("myDisabled");
                    }


                }
            }

        });





    }

    var configRevField = function (e) {

        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCREVSTATUS, [projectParams.ProjectCode], function (rows) {
            var revStatusCustomer = $(e).find("[mark=RevStatusCustomer]");
            var currentVal = revStatusCustomer.val();
            revStatusCustomer.empty();

            var isValueExist = rows.filter(function(row){ return row.REVISION_STATUS == currentVal}).length > 0;
            if(!isValueExist) revStatusCustomer.append("<OPTION DISABLED SELECTED VALUE='' ></OPTION>");

            var revStatusInternal = $(e).find("[mark=RevStatusInternal]");
            var currentValInternal = revStatusInternal.val();
            revStatusInternal.empty();

            var isValueExistInternal = rows.filter(function(row){ return row.REVISION_STATUS == currentValInternal}).length > 0;
            if(!isValueExistInternal) revStatusInternal.append("<OPTION DISABLED SELECTED VALUE='' ></OPTION>");

            rows.forEach(function (row) {
                var value = row.REVISION_STATUS;
                if (value == currentVal)
                    revStatusCustomer.append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                else
                    revStatusCustomer.append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");

                if (value == currentValInternal)
                    revStatusInternal.append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                else
                    revStatusInternal.append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
            });
        });


        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCSUBSTATUS, [projectParams.ProjectCode], function (data) {

            var subStatus = $(e).find("[mark=SubStatus]");
            var currentVal = subStatus.val();
            subStatus.empty();

            var isValueExist = data.filter(function(row){ return row.SUBMISSION_STATUS == currentVal}).length > 0;
            if(!isValueExist) subStatus.append("<OPTION DISABLED SELECTED VALUE='' ></OPTION>");

            data.forEach(function (row) {
                var value = row.SUBMISSION_STATUS;
                if (value == currentVal)
                    subStatus.append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                else
                    subStatus.append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
            });


            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_REVISION, [projectParams.ProjectCode, subStatus.val()], function (data) {
                var revID = $(e).find("[mark=Revision]").attr("id");
                $("#" + revID).find("ul").empty();
                var filter = new FilterSelect({
                    id: revID,
                    data: data,
                    showValList: 'REVISION'
                }, "select");
            });


        });


    }

    var addFolder = function () {
        var $this = $(this);
        var $wrapper = $this.closest(".document-item");
        var fldID = $wrapper.find("[fieldname=FLDID]").val();

        if (fldID != undefined && fldID.length > 0) {
            var url = projectParams.urlWL + "/Open/" + fldID + "/?NEXTURL=" + encodeURI(location.protocol + '//' + location.host + location.pathname);
            window.open(url)
        } else {

            ShowIlyasMessage("System", [
                '<select class="system-list" id="systemList" disabled><option disabled value selected>Загрузка списка</option></select>',
            ].join(""), null, function (modalWindow) {


                var value = $(modalWindow).find("#systemList").val();
                console.log("Selected value", value)

                if (value == undefined || value.length == 0) return

                Click_Save(document.myForm, null, function () {
                    var nextURL = location.protocol + '//' + location.host + "/" + "otcs/cs.exe/Open/" + projectParams.dataIdForm + "/?nexturl=" + findGetParameter("nexturl");
                    console.log(nextURL);
                    var url = makeURL("ll", {
                        objType: 0,
                        objAction: 'create',
                        parentId: value,
                        nextURL: encodeURI(nextURL)
                    });


                    window.open(url, "_self");


                });




            }, function (modalWindow) {
                //

                LookupManager.get(LookupManager.FUNCTIONS.ZEDM_SYSTEMS, [projectParams.ProjectCode], function (data) {
                    $(modalWindow).find("#systemList").empty().removeAttr("disabled").selectize({
                        options: data,
                        maxOptions: 10,
                        placeholder: "Введите систему",
                        create: false,
                        allowEmptyOption: false,
                        valueField: 'DATAID',
                        labelField: 'NAME',
                        searchField: 'NAME'
                    });

                })

            })
        }

    }

    getAlbums({
        onSuccess: function (albums) {
            $(".document-item").each(function (i, e) {

                configAttachField(e);
                configAlbumField(e, albums);
                configRevField(e);
                $(e).find(".add-filter-button").click(addFolder);

                $(e).find(".row-id").empty().append((i + 1));


                if (i == $(".document-item").length - 1) {
                    setTimeout(reloadTaskDocs, 500);
                }

            })
        },
        onError: function (error) {

            $(".document-item").each(function (i, e) {

                configAttachField(e);
                configAlbumField(e, []);
                configRevField(e);
                $(e).find(".add-filter-button").click(addFolder);

                $(e).find(".row-id").empty().append((i + 1));

                if (i == $(".document-item").length - 1) {
                    setTimeout(reloadTaskDocs, 500);
                }

            })
        }
    })


}

function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
}

function reloadTasks() {

    console.log("Reload tasks");

    // return;

    var tasksWrapper = $("#Tasks .avDivOuter:first .avDivMain:first");
    console.log(tasksWrapper);


    var reloadTask = function (oTaskEl) {
        console.log("Reload task", oTaskEl);

        // Fill date
        var taskDateVal = $(oTaskEl.find("[fieldname=TASKDUEDATE]")).val();
        var answerVal = $("#_1_1_93_1").val();
        if ((taskDateVal == undefined || taskDateVal.length == 0) && (answerVal != undefined && answerVal.length > 0)) {
            $(oTaskEl.find("[fieldname=TASKDUEDATE]")).attr("value", answerVal);
            fillDate($(oTaskEl.find("[fieldname=TASKDUEDATE]")).attr("name"));
        }
        fillDate($(oTaskEl.find("[fieldname=TASKDUEDATE]")).attr("name"));


        // Config TaskDueDate
        oTaskEl.find(".task-datepicker:first").datepicker({
            numberOfMonths: 2,
            showButtonPanel: false,
            showAnim: "show",
            dateFormat: "dd/mm/yy",
            showOn: "both",
            changeMonth: true,
            changeYear: true,
            buttonImage: "/img/datepicker.gif",
            buttonImageOnly: true,
            autoSize: true,
            minDate: new Date(0, 0, 1),
            maxDate: new Date(9999, 11, 31),
            monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
            monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
            dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            dayNamesShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
            prevText: "Назад",
            nextText: "Вперед",
            firstDay: 1
        });

        // Config task-performer
        // var TaskPerformerField = $(oTaskEl.find(".task-performer:first"));
        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_TASKSPERFORMER, [projectParams.ProjectCode], function (rows) {

            // Remove empty values
            rows = rows.filter(function (row) {
                return row.FIO != undefined && row.FIO.length > 1;
            });


            $(oTaskEl.find(".task-performer")).each(function (i, el) {


                var TaskPerformerField = $(el);

                TaskPerformerField.click(function () {
                    oTaskEl.find("[fieldname='IS_FIXED']").attr("value", "true");
                });

                // Save old value
                var currentVal = TaskPerformerField.find("[name-value=ID]").val().trim();
                var filter = new FilterSelect({
                    id: TaskPerformerField.attr("id"),
                    data: rows,
                    showValList: 'FIO'
                });

                // Remove loading state
                TaskPerformerField.find("input[type=text]").removeClass("loading-state");

                // If new data contains old value => set new title
                var oldRow = rows.find(function (row) {
                    return row.ID == currentVal;
                });
                if (oldRow != undefined) {
                    TaskPerformerField.find("input[type=text]").attr("value", oldRow.FIO);
                    TaskPerformerField.find("input[name-value=FIO]").attr("value", oldRow.FIO);
                }
            })



        });


        // Config task type filed

        // Replace if field is hidden
        var hiddenSelector = $(oTaskEl.find(".task-type-selector-hidden:first"));

        var newSelect = $('<select></select>').attr({
            name: $(hiddenSelector).attr("name"),
            value: $(hiddenSelector).attr("value"),
            class: "selectMenu task-type-selector loading-state"
        });

        if ($(hiddenSelector).attr("value") != undefined && $(hiddenSelector).attr("value").length > 1) {
            newSelect.append("<option value='" + $(hiddenSelector).attr("value") + "' selected>" + $(hiddenSelector).attr("value") + "</option>")
        }


        $(hiddenSelector).replaceWith($(newSelect));

        // Load data
        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_TASKTYPE, [projectParams.ProjectCode], function (rows) {

            var currentVal = $(newSelect).val();
            $(newSelect).empty();

            rows.forEach(function (row) {
                var value = row.TASKTYPE;
                if (value == currentVal)
                    $(newSelect).append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                else
                    $(newSelect).append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
            });
            $(newSelect).removeClass("loading-state");

        });


        // Engeneer document
        var docOldField = $(oTaskEl.find(".engDoc:first"));
        console.log(docOldField);
        if (docOldField.length == 1) {
            var selectDoc = $('<select></select>').attr({
                name: $(docOldField).attr("name"),
                id: $(docOldField).attr("id"),
                class: "selectMenu engDocTask"
            });


            var DOCIDField = docOldField.closest("div").find("[fieldname=DOCID]");
            var DNAMEField = docOldField.closest("div").find("[fieldname=DNAME]");

            selectDoc.change(function () {
                // alert(123);
                var DOCID = $(this).val();
                var DNAME = $(this).find("option:selected").text();

                console.log(DOCID, DNAME);
                console.log("dataWr", DOCID, DNAME);

                $(DOCIDField).attr("value", DOCID);
                $(DNAMEField).attr("value", DNAME);

                var dataWr = documents.find(function (doc) {
                    return doc.cdid == DOCID;
                });

                console.log("dataWr", dataWr);


                if (dataWr != undefined) {
                    var data = dataWr.data;
                    console.log("Selected doc", data);

                    var currentPerformer = $(oTaskEl.find(".Performer:first [name-value=ID]")).val();
                    console.log("currentPerformer", currentPerformer);

                    var isFixed = oTaskEl.find("[fieldname='IS_FIXED']").val() == "true";
                    console.log("isFixed", isFixed);

                    if (data != undefined && (currentPerformer == undefined || currentPerformer.length == 0 || !isFixed)) {

                        $(oTaskEl.find(".Performer:first [name-value=ID]")).attr("value", data.PERFORMER.ID);
                        $(oTaskEl.find(".Performer:first [name-value=FIO]")).attr("value", data.PERFORMER.NAME);
                        $(oTaskEl.find(".Performer:first .valueTag:first")).attr("value", data.PERFORMER.NAME);
                    }
                }


            });

            var DOCIDCurr = $(DOCIDField).val();
            var DNAMECurr = $(DNAMEField).val();
            console.log(DOCIDCurr, DNAMECurr);

            selectDoc.append("<option selected value='" + DOCIDCurr + "'>" + DNAMECurr + "</option>")

            docOldField.replaceWith($(selectDoc));
        }


    }

    tasksWrapper.find(".task-wrapper").each(function (taskIndex, taskEl) {
        reloadTask($(taskEl));
        if (taskIndex == tasksWrapper.find(".task-wrapper").length - 1) reloadTaskDocs();

    });


}

function reloadTaskDocs() {


    documents = [];

    $(".document-item").each(function (documentIndex, documentRow) {

        var albumNum = $(documentRow).find(".album-num:first").val();
        var listNum = $(documentRow).find(".list-field:first").val();
        var revisionNum = $(documentRow).find("[mark=Revision] [name-value=REVISION]").val();
        var cdidNum = $(documentRow).find("[fieldname=CDID]").val();

        var selectize = $(documentRow).find(".list-field:first").get(0).selectize;

        if (selectize != undefined) {
            var values = Object.values || valuesPolyfill;

            var data = values(selectize.options).find(function (datas) {
                return datas.CDDATAID == cdidNum;
            });

            console.log("cdidNum", cdidNum);
            console.log("selectize", values($(documentRow).find(".list-field:first").get(0).selectize.options));
            console.log("REload tasks docs", data);

            if (albumNum != undefined && albumNum.length > 0 && cdidNum != undefined && cdidNum.length > 0 && data != undefined) {
                documents.push({
                    album: albumNum,
                    list: listNum,
                    revision: revisionNum,
                    cdid: cdidNum,
                    data: data
                });
            }
        }


    });



    $(".engDocTask").each(function (engDocTaskIndex, engDocTask) {

        var valll = $(engDocTask).val();
        var currentVal = (' ' + valll).slice(1);

        console.log("HHHHH00", currentVal);
        console.log("HHHHH01", engDocTask);


        $(engDocTask).empty();


        if (documents.length > 0) {
            $(engDocTask).removeAttr("readonly");

            $(engDocTask).append(documents.reduce(function (prev, current) {

                console.log("HHHHH1", current);

                var newStr = prev;


                newStr += "<option value='" + current.cdid + "' " + ((current.cdid == currentVal) ? "selected" : "") + ">";
                newStr += current.album;

                if (current.list) newStr += "-" + current.list;

                newStr += "-" + current.revision;
                newStr += "</option>";
                return newStr;

            }, ""));



        } else {
            $(engDocTask).attr("readonly", "true");
            $(engDocTask).append("<option disabled selected value='" + currentVal + "'>Нет документов</option>");
        }

        $(engDocTask).change();

    });
}

var ElementDatePicker = {
    ElementDate: {},
    ElementUpdate: function (namePref, newDate) {
        if (namePref == null) {
            return;
        }

        $('#' + namePref + '_dirtyFlag').val('1');

        if ($('#' + namePref + ' option:selected').index() == 0) {
            this.NoSelection(namePref);
        } else {
            var year;
            var theDate = newDate;

            if (newDate == null && this.ElementDate[namePref] != undefined)
                theDate = this.ElementDate[namePref];


            if ($('#_datepicker_' + namePref).val() == "") {
                var thisDate = new Date();
                this.ElementDate[namePref] = thisDate;
                $('#_datepicker_' + namePref).val(thisDate.getDate() + '/' + thisDate.getDate() + '/' + thisDate.getDate());
                this.ScriptDate(namePref, theDate.getFullYear(), theDate.getMonth() + 1, theDate.getDate());
                $('#' + namePref).val($('#' + namePref).val() + '0:0:0');
            }

            console.log(theDate);

            this.UpdateMinuteHour(namePref, theDate);
            this.ConstructOScriptDate(namePref);
        }

        if (typeof (window.markDirty) != "undefined") {
            markDirty();
        }

    },
    NoSelection: function (namePref) {
        this.ClearDate(namePref);

        if ($('#' + namePref + '_hour option:selected').index() > 0) {
            $('select#' + namePref + '_hour')[0].selectedIndex = 0
        }

        if ($('#' + namePref + '_minute option:selected').index() > 0) {
            $('select#' + namePref + '_minute')[0].selectedIndex = 0
        }

        if ($('#' + namePref + '_second option:selected').index() > 0) {
            $('select#' + namePref + '_second')[0].selectedIndex = 0
        }

        if ($('#' + namePref + '_ampm option:selected').index() > 0) {
            $('select#' + namePref + '_ampm')[0].selectedIndex = 0
        }

        $('#namePref').val('');
    },

    ClearDate: function (namePref) {
        $('#_datepicker_' + namePref).datepicker("setDate", "");
        $('#_datepicker_' + namePref).val('');
        $('#' + namePref).val('');
        $('#' + namePref + '_dirtyFlag').val('');
    },

    ScriptDate: function (namePref, year, month, day) {
        $('#' + namePref).val('D/' + year + '/' + month + '/' + day + ':');
    },

    UpdateMinuteHour: function (namePref, newDate) {


        if (newDate.getHours() != null) $('#' + namePref + '_hour').val(newDate.getHours());
        else $('#' + namePref + '_hour').val(0);

        if (newDate.getMinutes() != null) $('#' + namePref + '_minute').val(newDate.getMinutes());
        else $('#' + namePref + '_minute').val(0);

        if (newDate.getSeconds() != null) $('#' + namePref + '_second').val(newDate.getSeconds());
        else $('#' + namePref + '_second').val(0);

    },

    ConstructOScriptDate: function (namePref) {
        this.ConstructDate(namePref, this.ElementDate[namePref])
    },

    ConstructDate: function (namePref, jsDate) {
        var timeSegment;
        this.ScriptDate(namePref, jsDate.getFullYear(), jsDate.getMonth() + 1, jsDate.getDate());
        timeArray = this.ConstructTime();
        $('#' + namePref).val($('#' + namePref).val() + timeArray[0] + ':' + timeArray[1] + ':' + timeArray[2]);
        this.ElementDate[namePref] = new Date(jsDate.getFullYear(), jsDate.getMonth(), jsDate.getDate(), timeArray[0], timeArray[1], timeArray[2], 0);
        $('#' + namePref + '_dirtyFlag').val('1');
    },

    ConstructTime: function () {
        var timeArray = new Array("0", "0", "0");
        return timeArray;
    }
}

var valuesPolyfill = function values(object) {
    return Object.keys(object).map(function (key) { return object[key]; });
};


if (!Array.prototype.find) {
    Object.defineProperty(Array.prototype, 'find', {
        value: function (predicate) {
            // 1. Let O be ? ToObject(this value).
            if (this == null) {
                throw new TypeError('"this" is null or not defined');
            }

            var o = Object(this);

            // 2. Let len be ? ToLength(? Get(O, "length")).
            var len = o.length >>> 0;

            // 3. If IsCallable(predicate) is false, throw a TypeError exception.
            if (typeof predicate !== 'function') {
                throw new TypeError('predicate must be a function');
            }

            // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
            var thisArg = arguments[1];

            // 5. Let k be 0.
            var k = 0;

            // 6. Repeat, while k < len
            while (k < len) {
                // a. Let Pk be ! ToString(k).
                // b. Let kValue be ? Get(O, Pk).
                // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
                // d. If testResult is true, return kValue.
                var kValue = o[k];
                if (predicate.call(thisArg, kValue, k, o)) {
                    return kValue;
                }
                // e. Increase k by 1.
                k++;
            }

            // 7. Return undefined.
            return undefined;
        }
    });
}

if (!Array.prototype.findIndex) {
    Object.defineProperty(Array.prototype, 'findIndex', {
        value: function (predicate) {
            // 1. Let O be ? ToObject(this value).
            if (this == null) {
                throw new TypeError('"this" is null or not defined');
            }

            var o = Object(this);

            // 2. Let len be ? ToLength(? Get(O, "length")).
            var len = o.length >>> 0;

            // 3. If IsCallable(predicate) is false, throw a TypeError exception.
            if (typeof predicate !== 'function') {
                throw new TypeError('predicate must be a function');
            }

            // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
            var thisArg = arguments[1];

            // 5. Let k be 0.
            var k = 0;

            // 6. Repeat, while k < len
            while (k < len) {
                // a. Let Pk be ! ToString(k).
                // b. Let kValue be ? Get(O, Pk).
                // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
                // d. If testResult is true, return k.
                var kValue = o[k];
                if (predicate.call(thisArg, kValue, k, o)) {
                    return k;
                }
                // e. Increase k by 1.
                k++;
            }

            // 7. Return -1.
            return -1;
        }
    });
}

var MultiRowAttr = {
    AddAttr: function (idAttr, callback) {
        var elSource = $(idAttr);
        var elParent = $(elSource).parent();
        var elNew = $(idAttr).clone();
        var inputs = $(elNew).find(':input');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].selectedIndex != null) {
                inputs[i].selectedIndex = 0;
            }
            else {
                $(inputs[i]).val('');
            }
        }

        var name = (elSource[0].name != null && elSource[0].name.length > 0) ? elSource[0].name : elSource[0].id;
        if (name != null && name.length > 0) {
            var prefix = '_1_1_' + $(elNew).attr('prefixrow');
            var reg = new RegExp(prefix + "\\d+");
            name = name.replace(new RegExp(reg), prefix + '0');
            elNew[0].name = elNew[0].id = name;
        }
        $(elNew).find('#delRow').css('display', 'inline');

        $(elSource).after(elNew);
        var newElId = this.RenameAttrs(elParent, name);
        if (callback) {
            callback(elNew);
        }
        if ($(elNew).attr('prefixrow') == "81_") dirNameUpdated();


    },
    DelAttr: function (idAttr, callback) {
        var elParent = $(idAttr).parent();
        $(idAttr).remove();
        var blocks = $(elParent).find('[prefixrow]');
        if (blocks.length == 1) {
            $(blocks[0]).find('#delRow').css('display', 'none');
        }

        this.RenameAttrs(elParent);
        if (callback) {
            callback(idAttr);
        }

        if ($(blocks[0]).attr('prefixrow') == "81_") dirNameUpdated();

    },
    RenameAttrs: function (parentElement, tempId) {
        var newElId;
        var blocks = $(parentElement).find('[prefixrow]');
        for (var i_b = 0; i_b < blocks.length; i_b++) {
            var prefix = '_1_1_' + $(blocks[i_b]).attr('prefixrow');
            var inputs = $(blocks[i_b]).find('*');
            var reg = new RegExp(prefix + "\\d+");
            var name = (blocks[i_b].name != null && blocks[i_b].name.length > 0) ? blocks[i_b].name : blocks[i_b].id;
            if (name != null && name.length > 0) {
                name = name.replace(new RegExp(reg), prefix + (i_b + 1));
                if (blocks[i_b].id == tempId) {
                    newElId = name;
                }
                blocks[i_b].name = blocks[i_b].id = name;
            }

            for (var i_i = 0; i_i < inputs.length; i_i++) {
                name = (inputs[i_i].name != null && inputs[i_i].name.length > 0) ? inputs[i_i].name : inputs[i_i].id;
                if (name == null || name.length == 0) {
                    continue;
                }
                name = name.replace(new RegExp(reg), prefix + (i_b + 1));
                if (name == null || name.length == 0) {
                    continue;
                }
                inputs[i_i].name = inputs[i_i].id = name;
            }
            if (blocks.length > 1) {
                $(blocks[i_b]).find('#delRow').css('display', 'inline');
            }
            var link = $(blocks[i_b]).find('#delRow')[0];
            var onclickData = $(link).attr('onclick').replace(new RegExp(reg), prefix + (i_b + 1));
            $(link).attr('onclick', onclickData);

            link = $(blocks[i_b]).find('#addRow')[0];
            var onclickData = $(link).attr('onclick').replace(new RegExp(reg), prefix + (i_b + 1));
            $(link).attr('onclick', onclickData);
        }
        return newElId;
    }
}

var checkRequiredFields = function (button, onSuccess) {

    if(isLoaderActive()){
        alert("Дождитесь завершения текущей задачи");
        return;
    }
    
    var afterSave = function(){
        if ($(button).attr("requred-fields") != undefined) {
            var requredFields = JSON.parse($(button).attr("requred-fields").replace(/\r?\n|\r|\t/g, ''));
            console.log("Requred fields", requredFields);
            var errors = [];
            var functions = [];

            requredFields.forEach(function (field) {

                if (field.hasOwnProperty("field")) {
                    var fieldObjs = $(field.field);

                    if (fieldObjs != undefined && fieldObjs.length == 1) {
                        var fieldObj = fieldObjs.first();

                        if (fieldObj != undefined && fieldObj.length > 0) {
                            var fieldIsFilled = fieldObj.val() != undefined && fieldObj.val().length > 0;
                            if (!fieldIsFilled) {
                                console.log("Field is not filled", fieldObj);
                                errors.push(field.description);
                            }
                        } else {
                            console.warn("[checkRequiredFields] field is not contains in the dom", field.description);
                        }

                    } else if (fieldObjs != undefined && fieldObjs.length > 1) {
                        console.log("muiltiple field", field.field);
                        console.log("muiltiple empty fields", fieldObjs.find("[value=]"));

                        var objectIsNotFilled = fieldObjs.filter("[value=]").length > 0;
                        if (objectIsNotFilled) {
                            console.log("Field is not filled", fieldObjs);
                            errors.push(field.description);
                        }
                    }


                } else if (field.hasOwnProperty("fields_or") && field.fields_or.length > 0) {

                    var filledField = field.fields_or.find(function (fieldOr) {
                        var fieldObj = $(fieldOr);
                        if (fieldObj != undefined && fieldObj.length > 0) {
                            return fieldObj.val() != undefined && fieldObj.val().length > 0;
                        } else {
                            console.warn("[checkRequiredFields] field is not contains in the dom", field.description);
                        }
                        return false
                    });
                    if (filledField == undefined) {
                        console.log("Field is not filled", field.fields_or);
                        errors.push(field.description);
                    };

                } else if (field.hasOwnProperty("fields_and") && field.fields_and.length > 0) {

                    var filledFields = field.fields_and.filter(function (fieldAnd) {
                        var fieldObj = $(fieldAnd);
                        if (fieldObj != undefined && fieldObj.length > 0) {
                            if (fieldObj.attr("type") == "checkbox") {
                                return fieldObj.is(":checked");
                            } else {
                                return fieldObj.val() != undefined && fieldObj.val().length > 0;
                            }
                        } else {
                            console.warn("[checkRequiredFields] field is not contains in the dom", field.description);
                        }
                        return false
                    });

                    console.log(filledFields);
                    if (filledFields.length == 0 || filledFields.length == field.fields_and.length) {

                    } else {
                        console.log("Field is not filled (All reqired or nothing)", field.fields_and);
                        errors.push(field.description);
                    }

                } else if (field.hasOwnProperty("element_exist")) {
                    if ($(field.element_exist).length == 0) errors.push(field.description);
                } else if (field.hasOwnProperty("function")) {
                    //if ($(field.element_exist).length == 0) errors.push(field.description);
                    functions.push(field.function);
                // errors.push("Test");
                }
            });

            if (errors.length == 0) {

                if(functions.length == 0){
                    onSuccess();
                    return;
                }else{

                    var functionsErrors = [];

                    toogleLoader({ show: true });

                    functions.forEach(function(functionName){
                        if(window[functionName] && isFunction(window[functionName])){
                            window[functionName](function(){
                                functionsErrors.push(null);
                            }, function(error){
                                functionsErrors.push(error);
                            })
                        }else{
                            console.error("Function "+functionName+" doesn't exist");
                            functionsErrors.push("Не найдена функция '"+functionName+"'");
                        }
                    });

                    var timerID = setInterval(function(){
                        if(functionsErrors.length == functions.length){
                            clearInterval(timerID);
                            toogleLoader({ show: false });
                            var cleanFunctionErrors = functionsErrors.filter(function(funcRes){ return funcRes != null });
                            if(cleanFunctionErrors.length > 0){
                                console.log(errors);
                                var veryCleanErrors = cleanFunctionErrors.filter(function(funcRes){ return funcRes != "HIDDEN" });
                                if(veryCleanErrors.length > 0) alert(veryCleanErrors.join(", "));
                                return;
                            }else{
                                onSuccess();
                                return;
                            }
                        }
                    }, 1000);

                    return;
                }


            } else {
                console.log(errors);
                alert("Не заполнены следующие поля: " + errors.join(", "));
                return;
            }

        }
        console.warn("[checkRequiredFields] no requred-fields attribute")
        onSuccess();
    }

    Click_Save(document.myForm, null, afterSave);

}


function checkRegNumExist(onSuccess, onError){
    var regNum = $("#_1_1_135_1").val();
    if(!regNum || regNum.length == 0){
        onError("Поле 'Регистрационный номер' не заполнено");
        return;
    }


    var workid = findGetParameter("workid");
    if(!workid) workid = projectParams.dataIdForm;
    


    LookupManager.get(LookupManager.FUNCTIONS.ZEDM_CHECKREGNUM, [projectParams.ProjectCode, regNum, workid], function (rows) {

        if(!rows[0] || !rows[0].STATUS){
            onSuccess();
            return;
        }
        
        ShowIlyasMessage("regEdit", [
            'Документ с таким Регистрационным № уже существует, статус документа "'+rows[0].STATUS+'"',
        ].join(""), null, function (modalWindow) {
            // On register
            onSuccess();
        }, null, function(modalWindow){
            // On edit
            onError("HIDDEN");
        });


       
    }, onError);
}

function CheckSysNumber(metodSave, param1, param2, param3) {

    toogleLoader({ show: true });

    var inputSysNumber = $('#_1_1_134_1');
    var valueSysNumber = inputSysNumber.val();
    if (valueSysNumber != null && valueSysNumber.length > 0) {
        metodSave(param1, param2, param3);
    } else {
        var typeValue = $('#_1_1_55_1').val();
        var subTypeIDValue = $('#_1_1_52_1').val();
        var direction = $('#_1_1_81_2').find("option:selected").text();

        $.getJSON('', {
            'func': 'zsabrisutils.GenerateRegNum',
            'nodeId': projectParams.dataIdForm,
            'Project': projectParams.ProjectCode,
            'Type': typeValue,
            'SubType': subTypeIDValue,
            'Direction': direction,
            'ContractorCode': projectParams.fromContractorCode,
            'NumberType': 'SysNumber'
        }, function (data) {
            if (data.ok) {
                inputSysNumber.val(data.data);
                metodSave(param1, param2, param3);
            } else {
                alert(data.errMsg);
            }
        });
    }
}

function isFunction(functionToCheck) {
    return functionToCheck && {}.toString.call(functionToCheck) === '[object Function]';
}

/**
 * 
 * @param {{onSuccess: function(any[]), onError: function(string)}} options 
 */
function getAlbums(options) {

    if (window.ALBUMS != undefined && window.ALBUMS.length > 0) {
        if (options.onSuccess) options.onSuccess(window.ALBUMS);
        return;
    }

    $.ajax({
        type: "POST",
        url: projectParams.urlWL + '/open/wrGetDICAlbum',
        dataType: "json",
        data: {
            ProjectCode: projectParams.ProjectCode
        },
        async: true,
        cache: false,
        success: function (response) {
            console.log(response.actualRows);

            if (response.actualRows <= 0) {
                if (options.onError) options.onError("Actual rows is 0");
                return;
            }

            if (response.actualRows > 0) {

                var rows = [];
                response.myRows.forEach(function (row) {
                    var indexOfExistsRow = rows.findIndex(function (existRow) {
                        return existRow.CUSTNUM == row.CUSTNUM;
                    });

                    var attachesIDs = (row.ATTACHDATAID != undefined) ? row.ATTACHDATAID.split("; ") : null;
                    var attachesNames = (row.ATTACHNAME != undefined) ? row.ATTACHNAME.split("; ") : null;

                    row.ATTACHES = (attachesIDs != undefined && attachesNames != undefined) ? attachesIDs.map(function (attachID, indexOfAttachID) {
                        return { ID: parseInt(attachID), NAME: attachesNames[indexOfAttachID] }
                    }) : null;

                    delete row.ATTACHDATAID;
                    delete row.ATTACHNAME;

                    row.PERFORMER = (row.MDLPERFORMER != undefined && row.MDLPERFORMERFIO != undefined) ? { ID: parseInt(row.MDLPERFORMER), NAME: row.MDLPERFORMERFIO } : null;
                    delete row.MDLPERFORMER;
                    delete row.MDLPERFORMERFIO;

                    var LIST = {
                        CDDATAID: (row.CDDATAID != undefined) ? parseInt(row.CDDATAID) : null,
                        DIR: row.DIR,
                        LASTREVISION: row.LASTREVISION,
                        LASTREVISIONINTERNAL: (row.LASTREVISIONINTERNAL != undefined) ? parseInt(row.LASTREVISIONINTERNAL) : null,
                        LIST: ((row.LIST == null) ? "" : row.LIST),
                        REVSTATUSCUSTOMER: row.REVSTATUSCUSTOMER,
                        REVSTATUSINTERNAL: row.REVSTATUSINTERNAL,
                        SUBSTATUS: row.SUBSTATUS,
                        PERFORMER: row.PERFORMER,
                        ATTACHES: row.ATTACHES,
                    }

                    if (indexOfExistsRow == -1) {
                        indexOfExistsRow = rows.push({ CUSTNUM: row.CUSTNUM, FLDDATAID: row.FLDDATAID, LISTS: [LIST] }) - 1;
                    } else {
                        rows[indexOfExistsRow].LISTS.push(LIST);
                    }

                    rows[indexOfExistsRow].LISTS = rows[indexOfExistsRow].LISTS.filter(function (list) {
                        return list.CDDATAID != undefined;
                    })

                });

                window.ALBUMS = rows;

                if (options.onSuccess) options.onSuccess(rows);

            }




        }
    });
}

/**
 * Show modal window
 * @param {string} sType Type of message
 * @param {string} sMessage Text or html string
 * @param {string} [aSubMessage] Subtext (optional)
 * @param {function(jQuery)} cb Callback for accept button. It returns modal window object
 */
function ShowIlyasMessage(sType, sMessage, aSubMessage, cb, configBlock, onCancel) {
    var concatMessages = "", buttons = "", buttonCancel = "<a href='javascript:void(0);' class='button cancel'>Отмена</a>", buttonAccept = "<a href='javascript:void(0);' class='button accept'>Выбрать</a>"

    switch (sType) {
        case 'regEdit':
            buttons = "<a href='javascript:void(0);' class='button accept'>Зарегистировать</a>" + "<a href='javascript:void(0);' class='button cancel'>Изменить</a>";
            concatMessages += "<p>" + sMessage + "</p>";
            break;
        case 'error':
            buttons = buttonCancel;
            concatMessages += "<p>" + sMessage + "</p>";
            break;
        default:
            buttons = buttonAccept + buttonCancel;
            concatMessages += sMessage;
            break;
    }
    var overlay = $('<div id="dialog-overlay1" style="position: fixed;"></div>');
    var domModalWindow = $('<div id="dialog-box1" style="position: fixed;">' +
        '<div class="dialog-content">' +
        '<div id="dialog-message">' +
        concatMessages +
        '</div><div class="buttons-wrap">' +
        buttons +
        '</div></div>' +
        '</div>');

    $(domModalWindow.find('a')).on('click', function (event) {
        console.log(this, event)
        if ($.isFunction(cb) && !$(event.target).hasClass('cancel')) {
            cb(domModalWindow);
        }else if($.isFunction(cb) && $(event.target).hasClass('cancel') && onCancel){
            onCancel(domModalWindow);
        }
        this.domModalWindow.remove();
        this.overlay.remove();
    }.bind({
        domModalWindow: domModalWindow,
        overlay: overlay
    }));

    if (configBlock != undefined) {
        configBlock(domModalWindow);
    }

    var maskHeight = $(document).height();
    var maskWidth = $(document).width();


    overlay.appendTo($('body'));
    domModalWindow.appendTo($('body'));

    overlay.css({
        height: maskHeight,
        width: maskWidth
    });

    overlay.show();
    domModalWindow.show();
}

var makeURL = function (funcName, params) {
    var url = projectParams.urlWL + "?func=" + funcName;
    var optionsStr = Object.keys(params).reduce(function (prev, current) {
        return prev + "&" + current + "=" + params[current];
    }, "");
    return url + optionsStr;
}