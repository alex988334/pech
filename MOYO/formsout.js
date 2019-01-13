function OnClick_GetRegNum(setID, nodeId, project, typeID, subTypeID, direction, contractorCode) {
    var typeValue = $('#' + typeID).val();
    var subTypeIDValue = $('#' + subTypeID).val();


    fillRegDate();

    $("[name=GetRegNum]").addClass("myDisabled");


    SendFormWithoutReload(function () {

        $.getJSON('', {
            'func': 'zsabrisutils.GenerateRegNum',
            'nodeId': nodeId,
            'Project': project,
            'Type': typeValue,
            'SubType': subTypeIDValue,
            'Direction': direction,
            'ContractorCode': contractorCode
        }, function (data) {
            console.log(data)
            if (data.ok) {
                $('#' + setID).val(data.data);
                if (data.editable == 1) {
                    $('#' + setID).removeProp('readonly');
                    $("#_1_1_23_1").removeProp('readonly');
                    $("#_datepicker__1_1_33_1").datepicker("enable");
                    $("[name=GetRegNum]").removeClass("myDisabled");
                    
                }else{
                    setDisabledRowsForRegNum();
                }


            } else {
                alert(data.errMsg);
            }
        });
    })
    
}
function setDisabledRowsForRegNum () {
    //$('#_1_1_23_1').prop('readonly', true);
    $('#_1_1_30_1').prop('disabled', true);
    $('#_1_1_30_1').prop('readonly', true);
    //$('#_1_1_31_1').prop('disabled', true);
    //$('#_1_1_31_1').prop('readonly', true);

    $('.DirName').find('select').each(function (i, select) {
        $(select).prop('readonly', true);
        $(select).prop('disabled', true);
    })

    FilterSelect.getById('CC-2_1').setPlomb(true)
    if (!FilterSelect.getById('FIO-2_1') && !FilterSelect.getById('EMAIL-2_1')) {
        var interval = setInterval(function () {
            if (FilterSelect.getById('FIO-2_1') && FilterSelect.getById('EMAIL-2_1')) {
                FilterSelect.getById('FIO-2_1').setPlomb(true);
                FilterSelect.getById('EMAIL-2_1').setPlomb(true);
                clearInterval(interval);
            }
        }, 1000)
    }
    
}
function SendFormWithoutReload (cb) {
    document.myForm.LL_FUNC_VALUES.value = document.myForm.LL_FUNC_VALUES.value.replace(new RegExp(/Form.SubmitForm/), "Form.SaveForm");
   

    var saved_next_url = document.myForm.LL_FUNC_VALUES.value.match(new RegExp(/'nextUrl'='.*?'/i))[0]
    document.myForm.LL_FUNC_VALUES.value = document.myForm.LL_FUNC_VALUES.value.replace(new RegExp(/'nextUrl'='.*?'/i), "'nextUrl'='/otcs/cs.exe?func=llworkspace'");


    var jForm = $(document.myForm);



    var inputForDisabled = $("select[disabled]");

    inputForDisabled.removeAttr("disabled");


    $.ajax({
        type: "POST",
        url: jForm.attr('action'),
        data: jForm.serialize(),
        success: function(msg, type, info){
            document.myForm.LL_FUNC_VALUES.value = document.myForm.LL_FUNC_VALUES.value.replace(new RegExp(/'nextUrl'='.*?'/i), saved_next_url);

            toogleLoader({show: false});
            inputForDisabled.attr("disabled", true);

            if (info.status == 500) {
                ShowMessage('error', 'Ошибка!', ['Статус ответа сервера <strong>500</strong>']);
            } else {
                ShowMessage('message', 'Сохранено!');    
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
		if (i != -1)
		{
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
            if (win && !win.closed && win.SelectItem) {
                win.SelectItem = function (nodeID) {
                    modFunc(typeDoc, url, nodeID, callback, objectid);
                    win.close();
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
    var dateSend = $('#_1_1_38_1').val();
    toogleLoader({ show: true } );

    $.get(url + '/open/WRFormLinks', {
        objectid: objectid,
        addNodeDoc: (linkDocs.addDocs.length > 0) ? '[' + linkDocs.addDocs.join('],[') + ']' : '',
        addNodeEngDoc: (linkDocs.addEngDocs.length > 0) ? '[' + linkDocs.addEngDocs.join('],[') + ']' : '',
		delDocs: (linkDocs.delDocs.length > 0) ? '[' + linkDocs.delDocs.join('],[') + ']' : '',
        DateSend: dateSend,
		typeDoc: 'formOut'
    }, function (data) {
        //$('#LinkedDoc .avDivOuter').remove();
        $('#LinkedDoc .avDivOuter').empty().append(data).find(".avAShowOther").click();
        toogleLoader({ show: false } );

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
		if (i != -1)
		{
			linkDocs.addDocs.splice(i, 1);
			isNewEl = true;
		}
		var i = linkDocs.addEngDocs.indexOf(delValue);
		if (i != -1)
		{
			linkDocs.addEngDocs.splice(i, 1);
			isNewEl = true;
		}
		if (!isNewEl)
		{
			linkDocs.delDocs.push(delValue);
		}
		UpdateLink(url, objectid);
    }
}


function fillDateSend(){
    
    var $sendDateHidden = $("#_1_1_38_1");
    if($sendDateHidden.val() == undefined || $sendDateHidden.val().length == 0){

        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0!
        var yyyy = today.getFullYear();

        if(dd<10) dd = '0'+dd
        if(mm<10) mm = '0'+mm

        today = dd + '/' + mm + '/' + yyyy;
        $sendDateHidden.attr("value", "D/"+yyyy+"/"+mm+"/"+dd+":0:0:0");
        $('#_datepicker__1_1_38_1').attr("value", dd + '/' + mm + '/' + yyyy);
    }
}

function fillRegDate(){
    var $regDateHidden = $("#_1_1_33_1");
    if($regDateHidden.val() == undefined || $regDateHidden.val().length == 0){

        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0!
        var yyyy = today.getFullYear();

        if(dd<10) dd = '0'+dd
        if(mm<10) mm = '0'+mm

        today = dd + '/' + mm + '/' + yyyy;
        $regDateHidden.attr("value", "D/"+yyyy+"/"+mm+"/"+dd+":0:0:0");
        $('#_datepicker__1_1_33_1').attr("value", dd + '/' + mm + '/' + yyyy);
    }
}

/**
 * Show/hide loader view
 * @param {{show: boolean}} options 
 */
function toogleLoader(options){
    if(window.loaderView == undefined){
        window.loaderView = $("<div></div>");
        window.loaderView.addClass("loaderView");
    }
    if(options.show){
        $("body").append(window.loaderView);
    }else{
        window.loaderView.remove();
        window.loaderView = null;
    }
}

function isLoaderActive(){
    return (window.loaderView != undefined);
}

function fixCheckboxValues(){
    $("input[type=checkbox]").each(function(i, el){
        if($(el).val() == "on") $(el).attr("value", "1");
        else if ($(el).val() == "off") $(el).attr("value", "0");
    })
}

function removeEmptyFields(){
    $("input[name='']").remove();
}

function removeEmptyAlbumNum(){
    $(".album-num[fieldname=NUM] option[value=][selected]").remove();
}

function replaceNoneInAdresses(){
    $("[field-type=CONTACT]").filter(function(){return this.value=='<None>'}).attr("value", "")
}

function Click_Save(theForm, submitForm, notRedirect) {
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
    $('[name=_1_1_65_1').val(oDate.getFullYear());
    $('[name=_1_1_66_1').val((oDate.getMonth().toString().length > 1 ? oDate.getMonth().toString() : '0' + (oDate.getMonth() + 1).toString() + '. ') + aMonth[oDate.getMonth()]);
    
    removeEmptyDocsRows();
    fixCheckboxValues();
    removeEmptyFields();
    removeEmptyAlbumNum();
    replaceNoneInAdresses();
    
    toogleLoader({show: true});

    var DocumentsName = $('.documents-wrapper [ischanged="true"]');

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
                success: function(msg){
                  console.log(msg)
                }
              });
        })
    }
    if(theForm.LL_FUNC_VALUES){

        if (submitForm == null || !submitForm)
        {	
            if (myForm.LL_FUNC_VALUES.value.match(new RegExp(/'func'='formwf.saveform'/i)) && myForm.LL_FUNC_VALUES.value.match(new RegExp(/'func'='formwf.saveform'/i)).length > 0)
            {
                theForm.LL_FUNC_VALUES.value = theForm.LL_FUNC_VALUES.value.replace(new RegExp(/'LL_Editable'=false/i), "'LL_Editable'=true");
                var nextUrlReg = location.href.match(new RegExp(/NextURL=(.*)/i));
                if (nextUrlReg != null && nextUrlReg.length == 2)
                {
                    //theForm.LL_FUNC_VALUES.value = theForm.LL_FUNC_VALUES.value.replace(new RegExp(/'LL_NextURL'='.*?'/i), "'LL_NextURL'='" + decodeURIComponent(nextUrlReg[1]) + "'");
                }
            }
            else
            {
                theForm.LL_FUNC_VALUES.value = theForm.LL_FUNC_VALUES.value.replace(new RegExp(/Form.SubmitForm/), "Form.SaveForm");
            }
        }
        else
        {
            theForm.LL_FUNC_VALUES.value = theForm.LL_FUNC_VALUES.value.replace(new RegExp(/Form.SaveForm/), "Form.SubmitForm");
        }
    }

    var workid = findGetParameter("workid");
    var $letterType = $("#_1_1_30_1");

    applyTextNode({ 
        onSuccess: function(){
            if ($.isFunction(notRedirect)) {
                SendFormWithoutReload(notRedirect);
            } else {
                theForm.submit();
            }
        },
        onError: function(error){
            alert(error);
        }
     })

    
}

/**
 * 
 * @param {{ onSuccess: function(any), onError: function(string) }} options 
 */
function applyTextNode(options){
    var textForm = $("[name=TextEditForm]");

    console.log("APPY TEXT NODE");
    var html = window.myEditor.save();
//   alert(html);
  // var html =  $('.ql-editor').html();
   textForm.find("#textfield").text(html);

  // alert(textForm.find("#textfield").text());


    $.ajax({
        type: textForm.attr('method'),
        url: textForm.attr('action'),
        data: textForm.serialize()
    }).done(options.onSuccess).fail(options.onError);
}

function removeEmptyDocsRows(){
   $(".document-item").each(function(i, docRow){
        var $docRow = $(docRow);
        var $albumSelect = $docRow.find("[fieldname=NUM]");
        var $attachIDSelect = $docRow.find("[fieldname=ATTACHID]");
        var $revisionSelect = $docRow.find("[fieldname=REVISION]");

        var isAlbumSelected = $albumSelect.val() != undefined && $albumSelect.val().length > 0 && ! ($revisionSelect.val() == "<пусто>" || $revisionSelect.val() == "<None>" );
        var isAttachIdSelected = $attachIDSelect.val() != undefined && $attachIDSelect.val().length > 0 && ! ($revisionSelect.val() == "<пусто>" || $revisionSelect.val() == "<None>" );
        var isRevisionSelected = $revisionSelect.val() != undefined && $revisionSelect.val().length > 0 && ! ($revisionSelect.val() == "<пусто>" || $revisionSelect.val() == "<None>" );
        
        if(!isAlbumSelected && !isAttachIdSelected && !isRevisionSelected) $docRow.remove();//find("select input").removeAttr("name");
    }); 
}

function Click_Reject(sUrl, iNodeId, sNextUrl) {
    document.location = sNextUrl;
}

function Click_Outgoing(url, iNodeId) {
    $.get(url + '/open/NewOutgoingDocWR', {
        objectid: iNodeId
    }, function (data) {

    });
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

var cachedGroupRows = [];

function reloadGroup(rows){

    console.log("reloadGroup", rows);

    if(rows == null || rows.length == 0) rows = cachedGroupRows;

    console.log("ZEDM_SENDGROUP", rows)
            
    var sElementId = 'sendGroup';
    var sType = $('[name=_1_1_30_1]').val();
    console.log("ZEDM_SENDGROUP", rows);

    //var data = rows.filter(function (item) { 
    //    return (item.DOCTYPE == sType) && (($('[name=_1_1_81_1]').val().length ? item.GroupContractorCode == $('[name=_1_1_81_1]').val() : true) || ($('[name=_1_1_82_1]').val().length ? item.GroupContractorCode == $('[name=_1_1_82_1]').val() : true));
   // })

    data = rows.filter(function(obj, pos, arr){
        var ids = arr.map(function(mapObj){
            return mapObj.ID
        })
        console.log("IDS, ", ids)
        return ids.indexOf(obj.ID) === pos;
    }).filter(function(row){
        return row.DOCTYPE == $("#_1_1_30_1").val();
    });

    console.log("ZEDM_SENDGROUP 2", data);
    var filter = new FilterSelect({
                    id: sElementId,
                    name: sElementId,
                    data: data,
                    showValList: 'NAME',
                    onComplite: function (sVal, oElement) {

                        var row = rows.find(function(subrow){
                            return subrow.NAME == sVal;
                        })

                        console.log("sVal", sVal)
                        console.log("oElement", oElement)
                        console.log("row", row)

                        $.post('/otcs/cs.exe/open/WRFormRecipientsOut', {
                            ProjectCode: projectParams.ProjectCode,
                            objectid: projectParams.dataIdForm,
                            SendGroupID: row.ID
                        }, function (data) {
                            delete window.needLoadRows;
                            delete window.aRowsForRec;

                            window.needLoadRows = []
                            window.aRowsForRec = []

                            console.log("TEST!", window.needLoadRows)

                            $("#RecipientsOut").empty().append(data);                            
                            console.log("TEST!", window.needLoadRows)

                            $("#sendGroup > .valueTag").attr("value", sVal);

                            showAllRows('Recipient','hiddenRow');


                            $("#RecipientsOut [fieldname=CONTRACORCODE]").each(function(i, el){
                                if(!$(el).val() || $(el).val().length == 0){
                                    $(el).closest(".contact-row").remove();
                                }
                            })

                        });

                    }
                });

    var sValue = filter.inputFilter.val();
    var lengthValues = false
    aRowsForRec.forEach(function (item, i) {
        if (i) {
            item.forEach(function (obj) {
                if (obj.length) lengthValues = true;
            })
        }
    }); 



  //  if (sValue.length > 0 && !filter.hasError || !data.length || lengthValues) {
    //    filter.setPlomb(true);
   // } else if(filter.isPlomb) {
    //    filter.setPlomb(false);
   // }
    filter.inputFilter.removeClass("loading-state");

    $("#sendGroup").removeClass("plombed");


}

function setGroupSend(aRowsForRec, bool) {

    var isNull = true;
    console.log("setGroupSend", arguments)
    if (bool) {
        aRowsForRec.forEach(function (item, i) {
            if (i) {
                item.forEach(function (obj) {
                    if (obj.length) isNull = false;
                })
            }
        });    
    }

    console.log("aRowsForRec", aRowsForRec)
    if (isNull) {
        LookupManager.get("ZEDM_SENDGROUP", [projectParams.ProjectCode], reloadGroup);
    }
}

function onChangeRecipientRow (sValue) {
    console.log(arguments)
    var NAME = FilterSelect.getById('FIO-' + this.rowId);
    var EMAIL = FilterSelect.getById('EMAIL-' + this.rowId);
    var data = contactsRows.filter(function (item) {
        if (sValue.length) {
            if (item.hasOwnProperty('CONTRACTOR_CODE')) {
                return item.CONTRACTOR_CODE == sValue;
            }
            return false;
        } else return true;
    })
    NAME.aData = data;
    NAME.setSectionList(NAME.aData, NAME.aSections);
    NAME.sortList();
    EMAIL.aData = data;
    EMAIL.setSectionList(EMAIL.aData, EMAIL.aSections);
    EMAIL.sortList();
    // var CC = FilterSelect.getById('CC-' + this.rowId);
    // this.list.empty();
    // this.aData = contactsRows.filter(function (item) {
    //     if (CC.sInputVal.length) {
    //         if (item.hasOwnProperty('CONTRACTOR_CODE')) {
    //             return item.CONTRACTOR_CODE == CC.sInputVal;
    //         }
    //         return false;
    //     } else return true;
    // })
    // this.setSectionList(this.aData, this.aSections);
    // this.sortList();
}


function setRecipientRow(sValue, oElement, objReturned) {

    var getName = this.showValList;
    if (!this.notUpdate) {
        if (getName == 'NAME') {
            if (objReturned && objReturned.hasOwnProperty('EMAIL')) {
                FilterSelect.getById('EMAIL-' + this.rowId).notUpdate = true;
                FilterSelect.getById('EMAIL-' + this.rowId).setValue(objReturned.EMAIL);
            }
            $('#POSITION-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('POSITION')) {
                $('#POSITION-' + this.rowId).val(objReturned.POSITION);
            }

            $('#Direction-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('CODE_OE')) {
                $('#Direction-' + this.rowId).val(objReturned.CODE_OE);
            }
            $('#Employee-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('EMPLOYEE')) {
                $('#Employee-' + this.rowId).val(objReturned.EMPLOYEE);
            }
            $('#oe_name-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('OE_NAME')) {
                $('#oe_name-' + this.rowId).val(objReturned.OE_NAME);
            }
            $('#USER-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('USER')) {
                $('#USER-' + this.rowId).val(objReturned.USER);
            }
            $('#Contact-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('CONTACT')) {
                $('#Contact-' + this.rowId).val(objReturned.CONTACT);
            }
            
            if (objReturned && objReturned.hasOwnProperty('CONTRACTOR_CODE')) {
                FilterSelect.getById('CC-' + this.rowId).setValue(objReturned.CONTRACTOR_CODE);
                FilterSelect.getById('CC-' + this.rowId).setPlomb(true);
            } else {
                FilterSelect.getById('CC-' + this.rowId).setValue('');
                FilterSelect.getById('CC-' + this.rowId).setPlomb(false);
            }
        } else if (getName == 'EMAIL') {
            console.log('aaaaa')
            if (this.hasError && this.hasError) {
                this.hasError = false;
                this.setError(false);
                FilterSelect.getById('FIO-' + this.rowId).notUpdate = true;
                FilterSelect.getById('FIO-' + this.rowId).setValue('');
                FilterSelect.getById('FIO-' + this.rowId).setPlomb(false);
                $('#POSITION-' + this.rowId).val("");
                $('#Direction-' + this.rowId).val("");
                $('#Employee-' + this.rowId).val("");
                $('#oe_name-' + this.rowId).val("");
                $('#USER-' + this.rowId).val("");
                $('#Contact-' + this.rowId).val("");
                FilterSelect.getById('CC-' + this.rowId).setValue('');
                FilterSelect.getById('CC-' + this.rowId).setPlomb(false);
            }
            if (objReturned && objReturned.hasOwnProperty('NAME')) {
                FilterSelect.getById('FIO-' + this.rowId).notUpdate = true;
                FilterSelect.getById('FIO-' + this.rowId).setValue(objReturned.NAME);
                FilterSelect.getById('FIO-' + this.rowId).setPlomb(false);
            }
            $('#POSITION-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('POSITION')) {
                $('#POSITION-' + this.rowId).val(objReturned.POSITION);
            }
            $('#Direction-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('CODE_OE')) {
                $('#Direction-' + this.rowId).val(objReturned.CODE_OE);
            }
            $('#Employee-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('EMPLOYEE')) {
                $('#Employee-' + this.rowId).val(objReturned.EMPLOYEE);
            }
            $('#oe_name-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('OE_NAME')) {
                $('#oe_name-' + this.rowId).val(objReturned.OE_NAME);
            }
            $('#USER-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('USER')) {
                $('#USER-' + this.rowId).val(objReturned.USER);
            }
            $('#Contact-' + this.rowId).val("");
            if (objReturned && objReturned.hasOwnProperty('CONTACT')) {
                $('#Contact-' + this.rowId).val(objReturned.CONTACT);
            }

            if (objReturned && objReturned.hasOwnProperty('CONTRACTOR_CODE')) {
                FilterSelect.getById('CC-' + this.rowId).setValue(objReturned.CONTRACTOR_CODE);
                FilterSelect.getById('CC-' + this.rowId).setPlomb(true);
            } else {
                FilterSelect.getById('CC-' + this.rowId).setValue('');
                FilterSelect.getById('CC-' + this.rowId).setPlomb(false);
            }
        }
    } else {
        delete this['notUpdate'];
    }
}

window.initContactsCache = null;
function loadInitContacts(onSuccess){

    if(window.initContactsCache){
        onSuccess(window.initContactsCache);
        return;
    }

    console.log("loadInitContacts");
    $("#Recipient").addClass("myDisabled");

    var fields = [/*"CONTRACTOR_CODE", "CONTACT"*/];

    LookupManager.get('ZEDM_CONTACTS', [projectParams.ProjectCode, projectParams.ProjectCode, projectParams.ProjectCode, "", '', ''], function(rows) {
       /* rows = rows.map(function(row){
            fields.forEach(function(field){
                if(row[field] == undefined || row[field].length == 0){
                    row[field] = "<None>";
                }
            })
            return row
        })*/
        window.initContactsCache = rows
        onSuccess(rows);
        $("#Recipient").removeClass("myDisabled");

    }.bind(this), function(){
        onSuccess([]);
        $("#Recipient").removeClass("myDisabled");
    });
}


function assembleContacts(){
    var users = [];
    $(".copy-row, .to-row").each(function(i, contactsRow){
        $contactsRow = $(contactsRow);
        var user = {};
        $contactsRow.find("input[field-type]").each(function(j, input){
            $input = $(input);
            user[$input.attr("field-type")] = $input.val();
        });
        users.push(user);
    })
    return users;
}



function setEmailProperties(){

    var contacts = assembleContacts();

    var userEnterMaxEmailSize = $("[name=_1_1_85_1]").val();
    if(userEnterMaxEmailSize != undefined && userEnterMaxEmailSize.length > 0 && userEnterMaxEmailSize != "0"  && userEnterMaxEmailSize != "false"){
        console.log("userEnterMaxEmailSize != 0   =>    return");
        return;
    }

    var contactsArchiveGroup = contacts
                                .groupBy(function(el){ return el.ARCHIVE_FORMAT; })
                                .filter( function(el){ return el.key != undefined && el.key.length > 0 });

    var maxArchiveArrayLength = Math.max.apply(Math, contactsArchiveGroup.map(function(o){return o.values.length; }));
    var archiveMode = contactsArchiveGroup.find( function(group){ return group.values.length == maxArchiveArrayLength } );
    if(archiveMode != undefined){
        $("#_1_1_24_1").val(archiveMode.key);
    }


    var contactsWithMinSize = contacts.filter(function(contact){ return parseInt(contact.MAXEMAILSIZE) > 0; });
    var minMailSize = Math.min.apply(Math, contactsWithMinSize.map(function(o){return o.MAXEMAILSIZE; }));
    if(minMailSize > 0 && minMailSize != Infinity){
        $("#_1_1_29_1").val(minMailSize);
    }

}

function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}


function initRecipiens (sId, sPropVal, data, preloadedOptions, contractors) {
    console.log("initRecipiens", arguments)
    if(contractors){
        contractors = contractors.map(function(cont){
            return {CONTRACTOR_CODE: cont.CONTRACTORCODE}
        })
    }

    console.log(contractors);

    var $hiddenElement = $('#' + sId);
    
    var $newElement = $('<select></select>').attr({
        id: $hiddenElement.attr("id"),
        name: $hiddenElement.attr("name"),
        fieldname: $hiddenElement.attr("fieldname"),
        "field-type": $hiddenElement.attr("field-type")
    });

    $newElement.addClass("selectMenu");


    // If old input has had a value => append it to the new select
    var initValue = undefined;
    if($hiddenElement.val() != undefined && $hiddenElement.val().length > 0){
        initValue = $hiddenElement.val();
        $newElement.append("<option selected value='"+$hiddenElement.val()+"'>"+$hiddenElement.val()+"</option>");
    }

    $hiddenElement.replaceWith($newElement);

    var initOptionsJSONStr = $newElement.closest(".avUlMiddle").find("script.contacts-init-data:first").html().replace(/(\r\n|\n|\r)/g,"");

    var optionsData = JSON.parse(initOptionsJSONStr);
    Object.keys(optionsData).map(function(objectKey, index) {
        var value = optionsData[objectKey];
        if(value.length == 0) value = undefined;
        optionsData[objectKey] = value;
    });

    if(preloadedOptions == undefined) preloadedOptions = [];
    preloadedOptions.push(optionsData);

    console.log([optionsData[sPropVal]]);

    if(sPropVal === "CONTRACTOR_CODE"){
        preloadedOptions = contractors;
    }else{
        var groups = [{label: 'Контактные лица', value: '1'}, {label: 'Участники проекта', value: '2'}, {label: 'Сотрудники', value: '3'}]
    }

    var createParam = false;
    if(sPropVal == "EMAIL"){
        createParam = function(input){
            return (validateEmail(input)) ? { 'EMAIL': input, 'IS_NEW': true, "CONTACT": "<None>", "EMPLOYEE": "<None>" } : null;            
        }
    }

    var selectize = $newElement.selectize({
        optgroups: groups,
        maxOptions: 20,
        create: createParam,
        items: [optionsData[sPropVal]],
        options: preloadedOptions,
        allowEmptyOption: false,
        preload: false,
        optgroupField:"TYPE",
        optgroupLabelField:"label",
        optgroupValueField:"value",
        loadingClass: "loading",
        valueField: sPropVal,
        labelField: sPropVal,
        searchField: sPropVal,
        loadThrottle: 500,
        onInitialize: function () {
            console.log(sId, 'selectize', this);
        },
        onFocus: function () {
            console.log('focus',arguments)
        },
        onChange:  function (newVal) {

            // if its contractor code => clean values from a email and name inputs
            if(this.settings.valueField == "CONTRACTOR_CODE"){

                this.$wrapper.closest(".avUlMiddle").find("[name=IS_USER_ENTERED]").attr("value", "true");

                $("#Recipient").addClass("myDisabled");

                var emailSel = this.$wrapper.closest(".avUlMiddle").find("[field-type=EMAIL]").get(0).selectize;
                var fioSel = this.$wrapper.closest(".avUlMiddle").find("[field-type=NAME]").get(0).selectize;

                var emailValue = emailSel.items[0];
                
                console.log("emailValue", emailValue);
                console.log("emailSel.userOptions", emailSel.userOptions);

                ///if((emailValue && emailSel.userOptions[emailValue] != true) || !emailValue){
                //    emailSel.clear(true)
                //    emailSel.clearOptions()
               // }

               var currentItem = emailSel.items[0];
               if(currentItem){
                    var currentOptionEmail = emailSel.options[currentItem];
               }

                emailSel.clearOptions()
                emailSel.clearCache()

                if(currentOptionEmail){
                    emailSel.addOption(currentOptionEmail);
                    emailSel.addItem(currentItem, true);
                }

                //fioSel.clear(true)
                fioSel.clearOptions()
                fioSel.clearCache()

                LookupManager.get('ZEDM_CONTACTS', [projectParams.ProjectCode, projectParams.ProjectCode, projectParams.ProjectCode, "", '', newVal], function(rows) {
                    rows = rows.map(function(row){
                        ["CONTRACTOR_CODE", "CONTACT"].forEach(function(field){
                            if(row[field] == undefined || row[field].length == 0){
                                row[field] = "<None>";
                            }
                        })
                        return row
                    })
                    
                    rows.forEach(function(row){
                        emailSel.addOption(row)
                        fioSel.addOption(row)
                    }.bind(this))
                    
                    emailSel.refreshOptions(false)
                    fioSel.refreshOptions(false)

                    $("#Recipient").removeClass("myDisabled");

                }.bind(this), function(){

                    $("#Recipient").removeClass("myDisabled");

                });

                return;
            }

            // Search row by a value
            var values = Object.values || valuesPolyfill;
            var changedContact = values(this.options).find(function (al) {
                return al[sPropVal] == newVal
            });

            if(changedContact != undefined){

                var contCodeSel = this.$wrapper.closest(".avUlMiddle").find("[field-type=CONTRACTOR_CODE]").get(0).selectize;
                if(this.options[newVal] && this.options[newVal].IS_NEW == true){

                    // Enable contractor code input
                    if(contCodeSel != undefined){
                        contCodeSel.enable()
                    } 
                    this.$wrapper.closest(".avUlMiddle").find("[name=IS_USER_ENTERED]").attr("value", "true");



                }else{

                    // Disable contractor code input
                    if(contCodeSel != undefined) contCodeSel.disable()
                     
                    console.log("contCodeSel.control", contCodeSel.$control)

                    this.$wrapper.closest(".avUlMiddle").find("[name=IS_USER_ENTERED]").attr("value", "");

                }


                // Get all inputs this filled field-type
                var $inputs = this.$wrapper.closest(".avUlMiddle").find("[field-type]");
                $inputs.each(function(i, input){
                    var $input = $(input);

                    // If its changed input => return
                    if($input.attr("id") == this.$input.attr("id")){
                        console.log("this is same field", $input.attr("id"));
                        return;
                    }

                    // If its selectize input reload data silent
                    var selectize = input.selectize;
                    if(selectize != undefined){
                        selectize.clear(true)
                        selectize.addOption(changedContact);
                        var newValue = changedContact[selectize.settings.valueField];
                        console.log("Set new value to selectize for", selectize.settings.valueField, newValue);
                        selectize.addItem( newValue, true);

                    }else{
                        // Else - just update value
                        var newValue = changedContact[$input.attr("field-type")];
                        if(newValue == undefined){
                            newValue = "";
                        }
                        console.log("Set new value to simple input for", $input.attr("field-type"), newValue);
                        $input.attr("value", newValue);
                    }

                }.bind(this));

                // var options
                var nameVal = this.$wrapper.closest(".avUlMiddle").find("[field-type=NAME]").val();
                console.log("NAME", nameVal)
                console.log("changedContact", changedContact)
                console.log("contCodeSel", contCodeSel)

                if(nameVal && nameVal.length > 0 && changedContact.OE_NAME && changedContact.OE_NAME.length > 0){
                    var val  =  changedContact.CONTRACTOR_CODE + " / " + changedContact.OE_NAME
                    contCodeSel.$control.find(".item").text(val)
                    contCodeSel.$control.attr("title", val)
                }

            }

            setEmailProperties();

        },
        load: function(query, callback) {

            if(this.settings.valueField == "CONTRACTOR_CODE") return;
            

            if (!query.length || query.length < 3) return callback();

            var contCodeSel = this.$wrapper.closest(".avUlMiddle").find("[field-type=CONTRACTOR_CODE]").get(0).selectize;
            var contCode = ''
            console.log(contCodeSel);
            if(!contCodeSel.$control.hasClass("disabled") && contCodeSel.items.length > 0){
                contCode = contCodeSel.items[0];
            }

            //4 - FIO
            //5 - EMAIL
            //6 - CONT
            var fioQuery = (this.settings.valueField == "NAME") ? query : '';
            var mailQuery = (this.settings.valueField == "EMAIL") ? query : '';


            LookupManager.get('ZEDM_CONTACTS', [projectParams.ProjectCode, projectParams.ProjectCode, projectParams.ProjectCode, fioQuery, mailQuery, contCode], function(rows) {
                callback(rows.slice(0, 20));
            }.bind(this), function(){
                initValue = undefined;
                callback();
            });


        }

    }).get(0).selectize;

    console.log("optionsData", optionsData); 
    var nameVal = selectize.$wrapper.closest(".avUlMiddle").find("[field-type=NAME]").val();
    if(sPropVal === "CONTRACTOR_CODE" && optionsData.EMAIL && optionsData.EMAIL.length > 0  && nameVal && nameVal.length > 0){
        selectize.disable()
    }

    if(sPropVal === "CONTRACTOR_CODE" && nameVal && nameVal.length > 0 && optionsData.DIRECTIONNAME && optionsData.DIRECTIONNAME.length > 0){

        var val  =  optionsData.CONTRACTOR_CODE + " / " + optionsData.DIRECTIONNAME
        selectize.$control.find(".item").text(val)
        selectize.$control.attr("title", val)

    }

    //if(sPropVal === "CONTRACTOR_CODE" && optionsData.TYPE == "3" && !optionsData.CONTACT && !optionsData.CONTRACTOR_CODE && !optionsData.EMAIL && !optionsData.EMPLOYEE && !optionsData.NAME){
    //    selectize.disable()
    //}

}

function afterRecipiensInit(){

    console.log("afterRecipiensInit !!!!");

    $(".avUlMiddle.to-row, .avUlMiddle.copy-row").each(function(i, row){
        $row = $(row);


        var contCodeSel = $row.find("[field-type=CONTRACTOR_CODE]").get(0).selectize;
        console.log(contCodeSel);
        if(!contCodeSel.$control.hasClass("disabled")){


            var value = contCodeSel.items[0];
            if(value){
                console.log("Custom entered value detected", value);
                //contCodeSel.clear(false);
                //contCodeSel.addItem(value, false);
                contCodeSel.trigger("change", value)
            }

        }
    })
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
        case '_1_1_40_1':
            needAnswer($(oDOMElement));
        break;
        case '_1_1_44_1':
            needDelivery($(oDOMElement));
        break;
        case 'WithEngDoc':
            toogleDocsTable($(oDOMElement));
            break;
        case "_1_1_30_1":
            reloadGroup();
            showButtons();
            typeChanged($(oDOMElement).val());
            setGroupSend(aRowsForRec);
            $("#_1_1_31_1").empty();
            $("#_1_1_31_1").addClass("loading-state");
            loadOptions("_1_1_31_1", [$("#_1_1_30_1").attr("value")]);
            checkTypeAndSubtypeLoaded();
            break;
        case "_1_1_31_1":
            checkTypeAndSubtypeLoaded();
            break;
        case "_1_1_47_1":
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
        FilterSelect.aFilters[revFieldID].setPlomb(true);

        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCSUBSTATUS, [projectParams.ProjectCode], function (rows) {
            var selectedSubStatus = rows.find(function (row) {
                return row.SUBMISSION_STATUS == $(oDOMElement).val();
            });
            console.log(selectedSubStatus);

            if (selectedSubStatus != undefined && selectedSubStatus.NEED_REVISION == "1") {
                LookupManager.get(LookupManager.FUNCTIONS.ZEDM_REVISION, [projectParams.ProjectCode, $(oDOMElement).val()], function (data) {


                    FilterSelect.aFilters[revFieldID].setPlomb(false);

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

        var typeValue = $("#_1_1_30_1").attr('value');
        var subtypeValue = $("#_1_1_31_1").attr('value');
        var datePickerValue = $('#_datepicker__1_1_39_1').attr('value');

        var typeIsFilled = typeof typeValue !== typeof undefined && typeValue !== false && typeValue.length > 0;
        var subTypeIsFilled = typeof subtypeValue !== typeof undefined && subtypeValue !== false && subtypeValue.length > 0;
        var datePickerIsFilled = typeof datePickerValue !== typeof undefined && datePickerValue !== false && datePickerValue.length > 0;

        if (subTypeIsFilled && typeIsFilled && !datePickerIsFilled)
            loadOptions("_1_1_39_1", [typeValue, subtypeValue]);
        else{
            $("#_datepicker__1_1_39_1").datepicker("enable");
            $('#_datepicker__1_1_39_1').val("");
        }


    } else {
        $("#_datepicker__1_1_39_1").datepicker("disable");

  

        $('#_datepicker__1_1_39_1').val("");
        $('#_datepicker__1_1_46_1').val("");
    }



}
function needDelivery(oElement) {
    if (oElement.is(':checked')) {

        var typeValue = $("#_1_1_30_1").attr('value');
        var subtypeValue = $("#_1_1_31_1").attr('value');
        var datePickerValue = $('#_datepicker__1_1_45_1').attr('value');

        var typeIsFilled = typeof typeValue !== typeof undefined && typeValue !== false && typeValue.length > 0;
        var subTypeIsFilled = typeof subtypeValue !== typeof undefined && subtypeValue !== false && subtypeValue.length > 0;
        var datePickerIsFilled = typeof datePickerValue !== typeof undefined && datePickerValue !== false && datePickerValue.length > 0;

        if (subTypeIsFilled && typeIsFilled && !datePickerIsFilled)
            loadOptions("_1_1_45_1", [typeValue, subtypeValue]);
        else{
            $("#_datepicker__1_1_45_1").datepicker("enable");
            $('#_datepicker__1_1_45_1').val("");
        }


    } else {
        $("#_datepicker__1_1_45_1").datepicker("disable");

  

        $('#_datepicker__1_1_45_1').val("");
        $('#_datepicker__1_1_41_1').val("");
    }



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

    if(dd < 10) dd = "0"+dd;
    if(mm < 10) mm = "0"+mm;


    return dd + separator + mm + separator + yyyy;
}

function timeStringFromDate(date, separator) {
    var hours = date.getHours() < 10 ? ("0" + date.getHours()) : date.getHours();
    var minutes = date.getMinutes() < 10 ? ("0" + date.getMinutes()) : date.getMinutes();
    var seconds = date.getSeconds() < 10 ? ("0" + date.getSeconds()) : date.getSeconds();

    return hours + separator + minutes + separator + seconds;
}


function initDatepicker(elem){
    $(elem).datepicker({
        numberOfMonths: 2,
        showButtonPanel: false,
        showAnim: "show",
        dateFormat: "dd/mm/yy",
        showOn: "both",
        changeMonth:true,
        changeYear:true,
        buttonImage: "/img/datepicker.gif",
        buttonImageOnly: true,
        autoSize: true,
        minDate: new Date(0, 0, 1),
        maxDate: new Date(9999, 11, 31),
        monthNamesShort:[ 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек' ],
        monthNames:[ 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь' ],
        dayNamesMin:[ 'Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб' ],
        dayNamesShort:[ 'Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб' ],
        dayNames:[ 'Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота' ],
        prevText: "Назад",
        nextText: "Вперед",
        firstDay: 1,
        disabled: true, 
		showCurrentAtPos: 1	
    });
}


/**
 * Function fixs bottom menu when user scrolls
 */
function fixBottomMenuOnScroll(){
    var scrollTop = $(this).scrollTop();
    var bottomMenuWrapper = $(".bottom-menu-wrapper:first");
    var bottomPosition = bottomMenuWrapper.offset().top;
    var screenHeight = $(this).height();
    var diff = bottomPosition - screenHeight + bottomMenuWrapper.height();
    var isFixed = bottomMenuWrapper.hasClass("fixed");
    
    if((diff < scrollTop) && isFixed)bottomMenuWrapper.removeClass("fixed");
    else if((diff >= scrollTop) && !isFixed) bottomMenuWrapper.addClass("fixed");
}


function loadTextNode(){

    var $textWrapper = $(".avDivEmailText");
    //$textWrapper.addClass("myDisabled");
    $textWrapper.hide();

    var url = window.location.search.split('&').filter(function(item){ return item.match('nexturl');});
    if (url.length) {
        url = decodeURIComponent(url[0].replace('nexturl=', ''))
    } else url = '';

    $.get( projectParams.urlWL, { func: "ll", objId: projectParams.bodyID, objAction: "textedit", viewType: 1, nextURL: url }, function(data){
        
        // Make new html element
        var documentElement = document.createElement( 'html' );
        documentElement.innerHTML = data;

        // Make for html jQuery object
        var jDocumentElement = $(documentElement);

        // Search script element
        var textfield = jDocumentElement.find("#textfield");
        var editorView = $("<div></div>");
        editorView.attr("id", "editor");
        editorView.html(textfield.val());

        var newForm = jDocumentElement.find("[name=TextEditForm]");
        var EditorType = jDocumentElement.find("[name=EditorType]").hide();
        var MimeTypeSelector = jDocumentElement.find("[name=MimeTypeSelector]").hide();
        var func = jDocumentElement.find("[name=func]");
        var objAction = jDocumentElement.find("[name=objAction]");
        var objID = jDocumentElement.find("[name=objID]");
        var nexturl = jDocumentElement.find("[name=nexturl]");
        var fileName = jDocumentElement.find("[name=fileName]");
        var mimeType = jDocumentElement.find("[name=mimeType]");
        var secureRequestToken = jDocumentElement.find("[name=secureRequestToken]");

        newForm.empty();
        newForm.append(EditorType);
        newForm.append(MimeTypeSelector);
        newForm.append(func);
        newForm.append(objAction);
        newForm.append(objID);
        newForm.append(nexturl);
        newForm.append(fileName);
        newForm.append(mimeType);
        newForm.append(secureRequestToken);
        newForm.append(textfield);
     //   newForm.append(editorView);

        $textWrapper.empty().append(newForm);

        tinymce.init({ 
            selector:'#textfield',
            plugins: ["autoresize", "lists"],
            autoresize_bottom_margin: 20,
            autoresize_max_height: 350,
            autoresize_min_height: 200,
            entity_encoding : "raw",
            menubar:false,
            statusbar: false,
            init_instance_callback : function(editor) {
                console.log("Editor: " + editor.id + " is now initialized.");
                window.myEditor = editor;
                $textWrapper.show();
            }
        });

    } );

}


function showAllRows(blockName, className){
    $('#'+blockName).find('.'+className).each(function(){ 
        if ($(this).is(':visible')){
            if ($(this).find('.avAShowOther') !== undefined ){
                $(this).find('.avAShowOther').text('Показать остальные');
            }
            if ($(this).is('a')){
                if ($(this).text() == 'Показать остальные'){
                    $(this).text('Скрыть остальное');
                }else{
                    $(this).text('Показать остальные');
                }
            }else{
                $(this).hide();
            }
        }else{
            $(this).show();
            if ($(this).find('.avAShowOther') !== undefined ){
                $(this).find('.avAShowOther').text('Скрыть остальное');
            }
        }
    });
    if( blockName == 'Recipient'){
        if ($('#'+blockName).find('.avAShowOther').text() == 'Показать остальные'){
            $('#'+blockName).find('.avAShowOther').text('Скрыть остальное');
        }else{
            $('#'+blockName).find('.avAShowOther').text('Показать остальные');
        }
    }
}

$(function () {

    delete DIC;
    $.ajax({
        type: "POST",
        dataType: "html",
        url: projectParams.urlWL + '/open/wrgetdic_json',
        data:{
            ProjectCode: projectParams.ProjectCode
        },
        async: true,
        cache: false,   
        success: function(response){
            var cleanScript = response.replace(/<\/?[^>]+(>|$)/g, "");
            eval(cleanScript);
        }
    });
    

    $.ajax({
        type: "POST",
        dataType: "html",
        url: projectParams.urlWL + '/open/WRGETDICFORM',
        data:{
            folderid: projectParams.folderid
        },
        async: true,
        cache: false,   
        success: function(response){
            var cleanScript = response.replace(/<\/?[^>]+(>|$)/g, "");
            eval(cleanScript);
        }
    });

    


    loadTextNode();
    setEmailProperties();

    $(window).scroll(fixBottomMenuOnScroll);
    setTimeout(function(){ fixBottomMenuOnScroll.bind(window); }, 0);
    
    var regNum = $("#_1_1_4_1").val();
    if(regNum != undefined && regNum.length > 0){
        $("[name=GetRegNum]").addClass("myDisabled");
    }else{
        $("[name=GetRegNum]").removeClass("myDisabled");
    }


    loadOptions("DM_INIT");
    $("#_1_1_48_1").attr("value", $(".DirName").length);

    var dueDateAnswerPicker = $("#_datepicker__1_1_39_1");
    initDatepicker(dueDateAnswerPicker);
    var needResponseField = $("input[name=_1_1_40_1]");
    var dueDateAnswerHiddenVal = $("#_1_1_39_1").val();
    if(dueDateAnswerHiddenVal != undefined && dueDateAnswerHiddenVal.length > 0){
        // If value alredy exist - set checkmark and update datepicker
        needResponseField.prop('checked', true);
        dueDateAnswerPicker.datepicker("enable");
        fillDate("_1_1_39_1");
    }else{
        dueDateAnswerPicker.datepicker("disable");
    }
    var dueDateDeliveryPicker = $("#_datepicker__1_1_45_1");
    initDatepicker(dueDateDeliveryPicker);
    var needDeliveryField = $("input[name=_1_1_44_1]");
    var dueDateDeliveryHiddenVal = $("#_1_1_45_1").val();
    if(dueDateDeliveryHiddenVal != undefined && dueDateDeliveryHiddenVal.length > 0){
        // If value alredy exist - set checkmark and update datepicker
        needResponseField.prop('checked', true);
        dueDateDeliveryPicker.datepicker("enable");
        fillDate("_1_1_45_1");
    }else{
        dueDateDeliveryPicker.datepicker("disable");
    }

    initDatepicker("#_datepicker__1_1_34_1");
    if($("#_1_1_34_1").val() == undefined || $("#_1_1_34_1").val().length <= 1){
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0!
        var yyyy = today.getFullYear();

        if(dd<10) dd = '0'+dd
        if(mm<10) mm = '0'+mm

        today = dd + '/' + mm + '/' + yyyy;
        $("#_1_1_34_1").attr("value", "D/"+yyyy+"/"+mm+"/"+dd+":0:0:0");
    }

    fillDate("_1_1_34_1");
    fillDate("_1_1_38_1");
    fillDate("_1_1_33_1");



    setInterval(function(){
        $(".document-item").each(function(i, e){
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


    // If documents exist => reload data in the docs table
    var docs = $(".documents-wrapper:first");
    if (docs != undefined) {
        $(".documents-wrapper:first").addClass("myDisabled");
        reloadDataInDocs(docs);


        var checkFlag = function() {
            if(window.ALBUMS == undefined) {
               window.setTimeout(checkFlag, 100); 
            } else {
                console.log("GOOOOOOOOOOO");
                setTimeout(function(){

                    checkEngDocs();

                    //engDocCheck($(".avButtonGreen[value=Проверить]").get());
                }, 2000);
            }
        }
        checkFlag();
        
    }

  

    var state = $("#_1_1_32_1").val();
    if(state == "Рассмотрение РП"){
        //Если при загрузке страницы значение поля Статус = «Рассмотрение РП», кнопку «Передать Руководителю» переименовать на «Руководителю Направления» и дополнительно отображать кнопку «Перенаправить».
        $("#DeliverAdministrator").attr("value", "Передать руководителю направления");
        // $("#ButtonSubmit .avDivRightFloat:first").append('<input class="avButtonGreen" type="button" value="Перенаправить" id="Redirect" onclick="if(checkRequiredFields(this)){Click_Redirect();}">');
        $("#Redirect").attr("requred-fields",  $("#DeliverAdministrator").attr("requred-fields"));
    }else if(state == "Рассмотрение"){
        //Если при загрузке страницы значение поля Статус = «Рассмотрение» кнопку «Передать Руководителю» не показывать, показывать кнопку «Перенаправить». 
        $("#DeliverAdministrator").hide();
        // $("#ButtonSubmit .avDivRightFloat:first").append('<input class="avButtonGreen" type="button" value="Перенаправить" id="Redirect" onclick="if(checkRequiredFields(this)){Click_Redirect();}">');
        $("#Redirect").attr("requred-fields",  $("#DeliverAdministrator").attr("requred-fields"));
    }
    showButtons();

    // Set current user to the registrator field if it is empty
    var registratorName = $("#_1_1_49_1_Name");
    var registratorID = $("[name=_1_1_49_1_ID]");
    var registratorSavedName = $("[name=_1_1_49_1_SavedName]");
    if((registratorName.val() == undefined || registratorName.val().length == 0) && (registratorID.val() == undefined || registratorID.val().length == 0) && (registratorSavedName.val() == undefined || registratorSavedName.val().length == 0)) {
        registratorName.attr("value", projectParams.currentUser.id);
        registratorID.attr("value", projectParams.currentUser.name);
        registratorSavedName.attr("value", projectParams.currentUser.id);
    }

    $("HTML, BODY").animate({ scrollTop: 0 }, 0);
    $(window).load(function() {
        $("HTML, BODY").animate({ scrollTop: 0 }, 0);
    });


    typeChanged($("#_1_1_30_1").val());


});


var LETTER_TYPE = {
    OUTGOING_MEMO: "6. Исходящая Служебная Записка",
    PROTOCOL: "7. Протокол"
}

var DEFAULT_GENERATE_DOC_URL = "LGENDOC";
var GENERATE_DOC_URLS = [
    {
        id: LETTER_TYPE.OUTGOING_MEMO,
        code: "SZGENDOC"
    },{
        id: LETTER_TYPE.PROTOCOL,
        code: "PGENDOC"
    }
]

var CURRENT_GENERATE_DOC_URL = DEFAULT_GENERATE_DOC_URL;
var DOESNT_GENERATE_PDF_VISIBLE = false;
var MATCHING_VISIBLE = false;
var TASKS_VISIBLE = false;

function typeChanged(value){
    console.log("3546typeChanged", value);

    DOESNT_GENERATE_PDF_VISIBLE = (value == LETTER_TYPE.OUTGOING_MEMO || value == LETTER_TYPE.PROTOCOL);
    MATCHING_VISIBLE = (value == LETTER_TYPE.OUTGOING_MEMO);
    TASKS_VISIBLE = (value == LETTER_TYPE.PROTOCOL);

    console.log("DOESNT_GENERATE_PDF_VISIBLE", DOESNT_GENERATE_PDF_VISIBLE);
    console.log("MATCHING_VISIBLE", MATCHING_VISIBLE);
    console.log("TASKS_VISIBLE", TASKS_VISIBLE);

    var urlFromList = GENERATE_DOC_URLS.find(function(obj){
        return (obj.id == value)
    });

    if(!urlFromList) urlFromList = DEFAULT_GENERATE_DOC_URL;
    else urlFromList = urlFromList.code;

    if(value == LETTER_TYPE.OUTGOING_MEMO && $("#_1_1_32_1").val() == "Согласование"){
        createButton("На доработку", "to_rework_button", function(){
            $("#_1_1_32_1").attr("value", "Доработка");
        });
    }else if($("#to_rework_button").length > 0){
        $("#to_rework_button").remove();
    }


    CURRENT_GENERATE_DOC_URL = urlFromList;

    toogleDontGenerateField();
    toogleMatchingField();
    toogleTasks();

}

var TASKS_ALREADY_LOADED = false;
function toogleTasks(){
    var wrapperId = "Tasks";
    var menuWrapperId = "tasks_point";

    if($("#"+wrapperId).length == 0 && TASKS_VISIBLE){

        TASKS_ALREADY_LOADED = true;

        var headerTasks = '<"SUBSEQ","PERFORMER","TASKTYPE","DOCID","DNAME","TTEXT","TASKDUEDATE","TASKSTATUS","TTYPE","IS_FIXED">';
        var fields = ['SUBSEQ','PERFORMER','TASKTYPE','DOCID','DNAME','TTEXT','TASKDUEDATE','TASKSTATUS','TTYPE',"IS_FIXED"];
        var selectors = "rowselecttasks";
        

        var getData = updateData(selectors, fields);
        var els = new Array(fields.length);
        els[fields.indexOf('TTYPE')] = 1;
        getData.push('"' + els.join('","') + '"');
        var recArray = 'V{' + headerTasks + '<' + getData.join('><') + '>}';

        

        toogleLoader({ show: true });

        $.post('/otcs/cs.exe/open/WRFormTask', {
            DSFILETYPE: 'oscript',
            DSREQUESTDATA: recArray,
            DSHEADINGSINC: 'true',
            typeDoc: "FormOut"
        }, function (data) {
           
            var $wrapper = $("<div></div>");
            $wrapper.attr("id", wrapperId);
            $wrapper.addClass("sidebarPanel");
            $wrapper.addClass("sidebarPanelIsOpen");
            $wrapper.append(data);

            $wrapper.insertAfter("#Recipients2");

            toogleLoader({ show: false });

            reloadTasks();

        });

        var $menuPoint = $("<li></li>");
        $menuPoint.attr("id", menuWrapperId);
        $menuPoint.append($('<a id="ed_tasks_menu" class="headerSection ed_title_link" href="#'+wrapperId+'" style="border-bottom: none; padding-bottom: 3px;">Поручения</a>'));

        $("#headerSections ul.avUlNav").append($menuPoint);

    }else if($("#"+wrapperId).length > 0 && !TASKS_VISIBLE){

        $("#"+wrapperId).remove();
        $("#"+menuWrapperId).remove();

    }else if($("#"+wrapperId).length > 0 && TASKS_VISIBLE && !TASKS_ALREADY_LOADED){

        TASKS_ALREADY_LOADED = true;

        reloadTasks();


        var $menuPoint = $("<li></li>");
        $menuPoint.attr("id", menuWrapperId);
        $menuPoint.append($('<a id="ed_tasks_menu" class="headerSection ed_title_link" href="#'+wrapperId+'" style="border-bottom: none; padding-bottom: 3px;">Поручения</a>'));

        setTimeout(function(){
            $("#headerSections ul.avUlNav").append($menuPoint);

        }, 3000);
    }

    

}

function toogleDontGenerateField(){
    console.log("toogleDontGenerateField");

    var wrapperId = "doesnt_generate_pdf_wrapper";
    if($("#"+wrapperId).length == 0 && DOESNT_GENERATE_PDF_VISIBLE){
        var $wrapper = $([
            '<div class="avDivTr avDivTrMiddle">',
                '<div class="avDivTd avDivTdFirst"><span>Не генерировать PDF</span></div>',
                '<div class="avDivTd avDivTdAttrMessage">',
                    '<input type="checkbox" id="_1_1_93_1" name="_1_1_93_1" onchange="markDirty(this);" data-checked="false">',
                    '<input type="HIDDEN" id="_1_1_93_1_Exists" name="_1_1_93_1_Exists" value="1">',
                    '<label for="_1_1_93_1"></label>',
                '</div>',
                '<div class="avDivTd avDivTdFirst"><span>&nbsp;</span></div>',
                '<div class="avDivTd avDivTdAttrMessage"><span>&nbsp;</span></div> ',        
            '</div>'
        ].join(""));

        $wrapper.attr("id", wrapperId)
        console.log("wrapper", $wrapper)
        $("#AttrMessage .avDivMain").append($wrapper);

        //dontGenPdf

        $("#_1_1_93_1").prop('checked', (parseInt(projectParams.dontGenPdf) == 1));

    }else if($("#"+wrapperId).length > 0 && !DOESNT_GENERATE_PDF_VISIBLE){
        $("#"+wrapperId).remove();
    }
}

function toogleMatchingField(){
    console.log("toogleMatchingField");
    var wrapperId = "matching_filed_wrapper";

    if($("#"+wrapperId).length == 0 && MATCHING_VISIBLE){
        var $wrapper = $([
            '<div class="avDivTr avDivTrMiddle">',
                '<div class="avDivTd avDivTdFirst"><span>Согласующий</span></div>',
                '<div class="avDivTd avDivTdAttrMessage">',
                    '<input type="hidden" value name="_1_1_91_1_Name" id="_1_1_91_1_Name" />',
                    '<select id="_1_1_91_1_ID" name="_1_1_91_1_ID"></select>',
                '</div>',
                '<div class="avDivTd avDivTdFirst"><span>&nbsp;</span></div>',
                '<div class="avDivTd avDivTdAttrMessage"><span>&nbsp;</span></div> ',        
            '</div>'
        ].join(""));

        $wrapper.attr("id", wrapperId)
        console.log("wrapper", $wrapper)
        $("#AttrMessage .avDivMain").append($wrapper);

    

        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_TASKSPERFORMER, [projectParams.ProjectCode], function (rows) {
            var $matchingFiled = $("#_1_1_91_1_ID");

            var selected = [];
            if(projectParams.matchingUser && projectParams.matchingUser.length > 0){
                selected = rows.filter(function(row){ return row.ID == projectParams.matchingUser });
            }

            console.log("selected", selected);

            var $sel = $matchingFiled.selectize({
                options: rows,
                maxOptions: 50,
                create: false,
                readOnly: true,
                //plugins: ['hidden_textfield'],
                allowEmptyOption: true,
                items: selected.map(function(row){ return row.ID; }),
                valueField: 'ID',
                labelField: 'FIO',
                searchField: 'FIO',
                onChange:  function (newVal) {
                    console.log(newVal);

                    if(!newVal || newVal.length == 0){
                        $("#_1_1_91_1_Name").attr("value", "<None>");
                        $("#_1_1_91_1_ID").attr("value", "<None>");
                    }

                    var row = rows.find(function(row){ return row.ID == newVal });
                    if(row) $("#_1_1_91_1_Name").attr("value", row.FIO);
                    else {
                        $("#_1_1_91_1_Name").attr("value", "<None>");
                        $("#_1_1_91_1_ID").attr("value", "<None>");
                    }
                }
              
            });

        });


        




    }else if($("#"+wrapperId).length > 0 && !MATCHING_VISIBLE){
        $("#"+wrapperId).remove();

    }
}

function generateOutgoingDocument(){

    Click_Save(document.myForm, null, function(){


        console.log("generateOutgoingDoc for", CURRENT_GENERATE_DOC_URL);
        //EXAMPLE http://pmapp67/otcs/cs.exe/open/ZEDM_RPR_LGENDOC?workflowid=4181990
        var uri = "/otcs/cs.exe/open/ZEDM_"+projectParams.ProjectCode+"_"+CURRENT_GENERATE_DOC_URL;

        var workid = findGetParameter("workid");
        if(workid) uri += "?workflowid="+workid;
        else uri += "?formdataid="+projectParams.dataIdForm;

    var win = window.open(uri, '_blank');
  win.focus();

    })


}


function createButton(text, id, func){
    var $button = $("<input />");
    $button.addClass("avButtonGreen");
    $button.attr("type", "button");
    $button.attr("value", text);
    $button.attr("id", id);
    $button.click(func);
    $("#ButtonSubmit > .avDivRightFloat").append($button);
}

function checkTypeAndSubtypeLoaded() { // Оставить?
    var typeValue = $("#_1_1_30_1").attr("value");
    var subtypeValue = $("#_1_1_31_1").attr("value");

    if (typeValue != undefined && typeValue.length > 0 && subtypeValue != undefined && subtypeValue.length) {
        loadOptions("_1_1_97_1", [typeValue, subtypeValue]);
    } else {
        var jqElement = $("[name=_1_1_97_1]")
        if(jqElement.val() != undefined && jqElement.val().length > 0){
            fillDate("_1_1_97_1");
            $("#_datepicker__1_1_97_1").datepicker("enable");
        }else{
            $("#_datepicker__1_1_97_1").datepicker("enable");
            $("#_datepicker__1_1_97_1").attr("value","");
        }

    }
}

function showButtons () {
  var stateType = $("#_1_1_30_1").val();
    console.log('GenerateXLSX', stateType)
    $("#GenerateXLSX").hide();
	
	//>>20.08.2018
    if ((stateType != "6. Исходящая Служебная Записка") && ((stateType != "7. Протокол")))
	{
			$("#GenerateXLSX").show();
	}
	//<<20.08.2018
    
    $("#GenerateDocumentTo").show();

}
function loadOptions(name, params) {
    var jqElement = $('[name=' + name + ']');
    switch (name) {
        case '_1_1_30_1': //TYPE
        var sId = '#' + name;

        var hiddenSelector = $(sId);
        
        var newSelect = $('<select></select>').attr({
            id: $(hiddenSelector).attr("id"),
            name: $(hiddenSelector).attr("name"),
            value: $(hiddenSelector).attr("value"),
            class: "selectMenu task-type-selector loading-state"
        });
        $(newSelect).on('change', markDirty.bind(this, newSelect));

        if($(hiddenSelector).attr("value") != undefined && $(hiddenSelector).attr("value").length > 1){
            newSelect.append( "<option value='"+$(hiddenSelector).attr("value")+"' selected>" + $(hiddenSelector).attr("value") + "</option>" )
        }
    
        
        $(hiddenSelector).replaceWith($(newSelect));
        showButtons()
        jqElement = $('[name=' + name + ']');
        // Load data
       
            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_TYPE, [], function (rows) {
                var currentVal = jqElement.val();
                jqElement.empty();

                var selecedExist = false;
                //rows.forEach(function (row) {
					for (var i = 0; i < 8; i++) {
					          if (DIC.ZEDM_TYPE[i][2] == "1") {
							   var value = DIC.ZEDM_TYPE[i][0];
                    //var value = row.TYPE;
                    if (value == currentVal) {
                        selecedExist = true;
                        jqElement.append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
						
                    } else 
                        jqElement.append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
							  }
					}
                //});

                if (!selecedExist) {
                    jqElement.prepend("<OPTION SELECTED DISABLED VALUE> Выберите тип сообщения </OPTION>");
                } else {
                    jqElement.prepend("<OPTION DISABLED VALUE> Выберите тип сообщения </OPTION>");
						}
                //}

                jqElement.removeClass("loading-state");
                checkFieldMulti(jqElement, 'TYPE', rows)
                checkTypeAndSubtypeLoaded();
            });

            
            break;
        case '_1_1_31_1': //SUBTYPE
            // LookupManager.get('ZEDM_SENDGROUP', [projectParams.ProjectCode], function (rows) { console.log('TYTYTYTYTYTYYTYTYTYTYYTYTYTYT',rows)})
            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_SUBTYPE, [projectParams.ProjectCode, params[0]], function (rows) {

                var currentVal = jqElement.val();
                jqElement.empty();
                var selecedExist = false;
                console.log(rows)
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
        /*case '_1_1_47_1': //DirName

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_DIRNAME, [projectParams.ProjectCode], function (rows) {
                var currentVal = jqElement.val();
                jqElement.empty();
                var selecedExist = false;

                rows.filter(function (e) {
                    return (e.DIRECTIONS != undefined && e.DIRECTIONS.length > 0);
                }).forEach(function (e) {
                    var value = e.DIRECTIONS;

                    if (value == currentVal) {
                        selecedExist = true;
                        jqElement.append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                    } else
                        jqElement.append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");

                });

                if (!selecedExist) {
                    jqElement.prepend("<OPTION SELECTED DISABLED VALUE> Выберите направление </OPTION>");
                } else {
                    jqElement.prepend("<OPTION DISABLED VALUE> Выберите направление </OPTION>");
                }

                jqElement.removeClass("loading-state");
                checkFieldMulti(jqElement, 'DIRECTIONS', rows)
            });

            break;*/
		 case 'DirName':
			
            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_DIRNAME, [projectParams.ProjectCode], function (rows) {
                var dirName = $('.DirName [title=DirNum]');
				for (var i = 0; i < dirName.length; i++)
				{
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
                        if (rows.length == 1 ) {
                            $(dirName[i]).prepend("<OPTION DISABLED VALUE> Выберите направление </OPTION>");
                            $(dirName[i]).find("[value='"+rows[0].DIRECTIONS+"']").prop("SELECTED", true);
                        }else{
                            $(dirName[i]).prepend("<OPTION SELECTED DISABLED VALUE> Выберите направление </OPTION>");
                        }
					} else {
						$(dirName[i]).prepend("<OPTION DISABLED VALUE> Выберите направление </OPTION>");
					}

                    $(dirName[i]).removeClass("loading-state");
                    $(dirName[i]).removeAttr("disabled");
					checkFieldMulti($(dirName[i]), 'DIRECTIONS', rows);
				}
            });

            break;
            case '_1_1_39_1': //DateAnswer

            $("#_datepicker__1_1_39_1").attr("value", "Загрузка");

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_DUEDATEANSWER, [projectParams.ProjectCode, projectParams.fromContractorCode, params[0], params[1]], function (rows) {



                if (rows.length > 0 && $("#_1_1_34_1").attr("value") != undefined) {
                    var dateSendStr = $("#_1_1_34_1").attr("value");

                    var answerDuration = parseInt(rows[0].ANSWERDURATION);


                    var date = parseBackendDate(dateSendStr);
                    var dateStr = dateStringFromDate(date, "-");
                    var timeStr = timeStringFromDate(date, ":");

                    var overload = {
                        'func': 'ZCALENDAR.add_days',
                        'startDate': dateStr,
                        'daysCount': answerDuration,
                        'zcontracts_calendar_id': DIC.ZEDM_CALENDAR[0],
                        'time': timeStr
                    };


                    $.getJSON('/otcs/cs.exe', overload, function (data) {

                        if (data.status == "success") {
                            var rightStr = data.ExpireDate.substring(0, data.ExpireDate.length - 8) + data.time;

                            var date = new Date(rightStr);
                            var dateStr = "D/" + date.getFullYear() + "/" + (date.getMonth() + 1) + "/" + date.getDate() + ":" + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
                            jqElement.attr("value", dateStr);
                            $("#_datepicker__1_1_39_1").attr("value", dateStringFromDate(date, "/"));
                            $("[name=_1_1_39_1_hour]").attr("value", date.getHours());
                            $("[name=_1_1_39_1_minute]").attr("value", date.getMinutes());
                            $("[name=_1_1_39_1_second]").attr("value", date.getSeconds());
                            // $('#_datepicker__1_1_39_1').removeAttr('readonly');
                            // $('#_datepicker__1_1_41_1').removeAttr('readonly');
                        }else{
                            $("#_datepicker__1_1_39_1").attr("value", "");
                        }
                        $("#_datepicker__1_1_39_1").datepicker("enable");
                        

                    });
                }else{
                    $("#_datepicker__1_1_39_1").attr("value", "");
                    $("#_datepicker__1_1_39_1").datepicker("enable");
                }

             }, function(error){
                $("#_datepicker__1_1_39_1").attr("value", "");
                $("#_datepicker__1_1_39_1").datepicker("enable");
            });


        break;
        case '_1_1_45_1': //DateAnswer

            $("#_datepicker__1_1_45_1").attr("value", "Загрузка");

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_DUEDATEANSWER, [projectParams.ProjectCode, projectParams.fromContractorCode, params[0], params[1]], function (rows) {


                if (rows.length > 0 && $("#_1_1_34_1").attr("value") != undefined) {
                    var dateSendStr = $("#_1_1_34_1").attr("value");

                    var answerDuration = parseInt(rows[0].ANSWERDURATION);

                    var date = parseBackendDate(dateSendStr);
                    var dateStr = dateStringFromDate(date, "-");
                    var timeStr = timeStringFromDate(date, ":");

                    var overload = {
                        'func': 'ZCALENDAR.add_days',
                        'startDate': dateStr,
                        'daysCount': answerDuration,
                        'zcontracts_calendar_id': DIC.ZEDM_CALENDAR[0],
                        'time': timeStr
                    };


                    $.getJSON('/otcs/cs.exe', overload, function (data) {

                        if (data.status == "success") {
                            var rightStr = data.ExpireDate.substring(0, data.ExpireDate.length - 8) + data.time;

                            var date = new Date(rightStr);
                            var dateStr = "D/" + date.getFullYear() + "/" + (date.getMonth() + 1) + "/" + date.getDate() + ":" + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
                            jqElement.attr("value", dateStr);
                            $("#_datepicker__1_1_45_1").attr("value", dateStringFromDate(date, "/"));
                            $("[name=_1_1_45_1_hour]").attr("value", date.getHours());
                            $("[name=_1_1_45_1_minute]").attr("value", date.getMinutes());
                            $("[name=_1_1_45_1_second]").attr("value", date.getSeconds());
                            // $('#_datepicker__1_1_45_1').removeAttr('readonly');
                            // $('#_datepicker__1_1_41_1').removeAttr('readonly');
                        }else{
                            $("#_datepicker__1_1_45_1").attr("value", "");
                        }
                        $("#_datepicker__1_1_45_1").datepicker("enable");
                        

                    });
                }else{
                    $("#_datepicker__1_1_45_1").attr("value", "");
                    $("#_datepicker__1_1_45_1").datepicker("enable");
                }

             }, function(error){
                $("#_datepicker__1_1_45_1").attr("value", "");
                $("#_datepicker__1_1_45_1").datepicker("enable");
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
        case '_1_1_36_1': //CONTRACT

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_CONTRACT, [projectParams.ProjectCode], function (rows) {
                var filter = new FilterSelect({
                    id: '_1_1_36_1',
                    name: '_1_1_36_1',
                    data: rows,
                    showValList: 'CONTRACT_NAME'
                })
                $('#_1_1_36_1').find("input[type=text]").removeClass("loading-state");

                var currentVal = $("[name=_1_1_36_1]").val();
                if (currentVal != undefined) {
                    if (rows.length == 1) {
                        filter.setValue(rows[0].CONTRACT_NAME)
                    } else {
                        var selectedRow = rows.find(function (row) {
                            return row.CONTRACT_NUM == currentVal;
                        });

                        if (selectedRow != undefined && selectedRow.CONTRACT_NAME != undefined)
                            $('#_1_1_36_1').find("input[type=text]").attr("value", selectedRow.CONTRACT_NAME);
                    }
                }


            });


            break;

        case '_1_1_50_1_59_1': //StepEngDoc

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCSUBSTATUS, [projectParams.ProjectCode], function (rows) {
                rows.forEach(function (e) {
                    var value = e.SUBMISSION_STATUS;
                    jqElement.append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
                });
            });

            break;
            // Documents fields
        case 'RevStatusCustomer':
            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCREVSTATUS, [projectParams.ProjectCode], function (rows) {
                $("[mark=RevStatusCustomer]").each(function (i, e) {
                    var currentVal = $(e).val();
                    $(e).empty();
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
                    data.forEach(function (row) {
                        var value = row.SUBMISSION_STATUS;
                        if (value == currentVal)
                            $(e).append("<OPTION SELECTED VALUE='" + value + "' >" + value + "</OPTION>");
                        else
                            $(e).append("<OPTION VALUE='" + value + "' >" + value + "</OPTION>");
                    });
                });
            });


            break;

        

        case "DM_INIT":
            var idField = $("[name=_1_1_82_1_ID]");
            console.log("_1_1_82_1_ID", idField.val());

            if(idField.val() != undefined && idField.val().length > 0){

                var fioss, namess;

                // Ищем группу по id
                console.log("Search group");
                for (var prop in DIC.ZEDM_DM) {
                    console.log("test group", prop);
                    if(DIC.ZEDM_DM[prop] != undefined && DIC.ZEDM_DM[prop].length > 0 && DIC.ZEDM_DM[prop][0] != undefined && DIC.ZEDM_DM[prop][0].length > 0 && DIC.ZEDM_DM[prop][0][0] != undefined && DIC.ZEDM_DM[prop][0][0] == idField.val()){
                        console.log("group is ok", DIC.ZEDM_DM[prop]);
                        fioss = DIC.ZEDM_DM[prop].map(function (row) {
                            return row[2];
                        }).join(", ");
                        namess =  DIC.ZEDM_DM[prop][0][1];
                        break;
                    }
                }

                console.log("fio name", fioss, namess);


                // Если не нашли ищем по taskperformer
                if(fioss == undefined && namess == undefined){
                    console.log("Search performer");
                    LookupManager.get(LookupManager.FUNCTIONS.ZEDM_TASKSPERFORMER, [projectParams.ProjectCode], function (rows) {
                        var row = rows.find(function(row){return row.ID == idField.val(); });

                    
                        if(row != undefined){

                            console.log("performer found", row);


                            $("#_1_1_82_1").attr("value", row.FIO);
                            $("#_1_1_82_1").attr("title", row.FIO);
                            $("[name=_1_1_82_1_Name]").attr("value", row.FIO);
                        }else{
                            console.log("performer is not found", row);

                        }
                    });
                }else{
                    console.log("Set group data");
                    $("#_1_1_82_1").attr("value", fioss);
                    $("#_1_1_82_1").attr("title", fioss);
                    $("[name=_1_1_82_1_Name]").attr("value", namess);
                }

            }

            break;

        case "DM":

            if($(".DirName").length > 1){
                $("#_1_1_82_1").attr("value", "");
                $("[name=_1_1_82_1_Name]").attr("value", "");
                $("[name=_1_1_82_1_ID]").attr("value", "");
                $("#_1_1_82_1").attr("title", "");
                break;
            }


            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_DM, [params[0], projectParams.ProjectCode], function (rows) {

                if(rows != undefined && rows.length > 0){
                    var name = rows[0].NAME;
                    var id = rows[0].ID;
                    var fio = rows.map(function (row) {
                        return row.FIO
                    }).join(", ");
                    $("#_1_1_82_1").attr("value", fio);
                    $("#_1_1_82_1").attr("title", fio);
                    $("[name=_1_1_82_1_Name]").attr("value", name);
                    $("[name=_1_1_82_1_ID]").attr("value", id);
                }else{
                    $("#_1_1_82_1").attr("value", "");
                    $("[name=_1_1_82_1_Name]").attr("value", "");
                    $("[name=_1_1_82_1_ID]").attr("value", "");
                    $("#_1_1_82_1").attr("title", "");
                }

            }, function(error){
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
                
                if(rows == undefined || rows.length == 0){
                    alert("Невозможно определить Руководителя проекта");
                    $("[name=GetPM]").prop( "checked", false );
                    return;
                }

                if(isInitialLoading){

                    if(rows[0].ID == $("[name=_1_1_7_1_ID]").val()){
                        $("[name=GetPM]").prop( "checked", true );
                        var names = rows.map(function (row) {
                            return row.FIO
                        }).join(", ");
                        $("[name=_1_1_7_1_Name]").attr("value", names);
                        $("[name=_1_1_7_1_Name]").attr("title", names);
                    }

                }else{
                    $("[name=_1_1_7_1_ID]").val("");
                    $("[name=_1_1_7_1_Name]").attr("value", "");
                    var names = rows.map(function (row) {
                        return row.FIO
                    }).join(", ");
                    $("[name=_1_1_7_1_Name]").attr("value", names);
                    $("[name=_1_1_7_1_Name]").attr("title", names);

                    if (rows[0] != undefined && rows[0].ID != undefined) $("[name=_1_1_7_1_ID]").val(rows[0].ID);
                }

                
            }, function(error){
                alert("Невозможно определить Руководителя проекта");
                $("[name=GetPM]").prop( "checked", false );
            })

            break;

        case "ShowLastAttibutes":



            var wrapper = $("#" + params[2]).closest(".document-item").find(".last-attributes:first");

            LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCLAST, [projectParams.ProjectCode, params[0], params[1]], function (rows) {
                if (rows[0] != undefined) {
                    var row = rows[0];
                    if (row.REVISION != undefined) {
                        wrapper.find("[mark=LastRevision]").val(row.REVISION);
                    } else {
                        wrapper.find("[mark=LastRevision]").val("");
                    }
                    if (row.REVISIONINTERNAL != undefined) {
                        wrapper.find("[mark=LastRevisionInternal]").val(row.REVISIONINTERNAL);
                    } else {
                        wrapper.find("[mark=LastRevisionInternal]").val("");
                    }
                    if (row.REVSTATUSINTERNAL != undefined) {
                        wrapper.find("[mark=LastRevStatusInternal]").val(row.REVSTATUSINTERNAL);
                    } else {
                        wrapper.find("[mark=LastRevStatusInternal]").val("Нет");
                    }
                    if (row.REVSTATUSCUSTOMER != undefined) {
                        wrapper.find("[mark=LastRevStatusCustomer]").val(row.REVSTATUSCUSTOMER);
                    } else {
                        wrapper.find("[mark=LastRevStatusCustomer]").val("Нет");
                    }
                    if (row.SUBSTATUS != undefined) {
                        wrapper.find("[mark=LastSubStatus]").val(row.SUBSTATUS);
                    } else {
                        wrapper.find("[mark=LastSubStatus]").val("Нет");
                    }
                } else {
                    var reason = "Нет";
                    wrapper.find("[mark=LastRevision]").val("");
                    wrapper.find("[mark=LastRevisionInternal]").val("");
                    wrapper.find("[mark=LastRevStatusInternal]").val("Нет");
                    wrapper.find("[mark=LastRevStatusCustomer]").val("Нет");
                    wrapper.find("[mark=LastSubStatus]").val("Нет");
                }
            }, function (error) {
                wrapper.find("[mark=LastRevision]").val("Ошибка");
                wrapper.find("[mark=LastRevisionInternal]").val("Ошибка");
                wrapper.find("[mark=LastRevStatusInternal]").val("Ошибка");
                wrapper.find("[mark=LastRevStatusCustomer]").val("Ошибка");
                wrapper.find("[mark=LastSubStatus]").val("Ошибка");
            });


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


function stringSelect_1_1_43_1() {
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
    if(PSPNRField != null){
        PSPNRField.value = DIC.ZEDM_SAPPROJECTCODE[0];
    }else{
        w.addEventListener('load', function(){
            var PSPNRField = this.document.getElementById("sapfield_PSPID");
            PSPNRField.value = DIC.ZEDM_SAPPROJECTCODE[0];
        });
    }

    
    function changeResultAction() {
        if (w.selectSapObject) {
            w.selectSapObject = function (key, nodeID) {
                $('[name="_1_1_43_1"]').val(nodeID);
                $('[name="_1_1_42_1"]').val(key);
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


function generateErrorsTable(mappedData){

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
        
        errorDom.append("<td>" + messages +"</td>");
       
        errorsDom.append(errorDom);
    });

    return errorsDom;
}

function checkEngDocs(){

    console.log("Check eng docs");

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
    var showErrors = function(){
        checkedRowsCount++;

        if(checkedRowsCount < $docsItems.length) return;

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

        if (totalErrors.length == 0){
            errorsBlock.append("<center style='color: #605b5b;'>Нет ошибок, для проверки нажмите кнопку 'Проверить'</center>");
        } else{
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
        if (!$attachField.val() || $attachField.val().length <= 0) {
            totalErrors.push({
                $input: $attachField,
                message: "Необходимо выбрать вложение",
                row: i
            });
        } else if (/[\u0400-\u04FF]/.test($titleField.val())){
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
        if(selectize != undefined){
            var values = Object.values || valuesPolyfill;
            listData = values(selectize.options).find(function(datas){ return datas.CDDATAID == $cdidField.val(); });
        }

        // Check list
        if(listData == undefined || listData.CDDATAID == undefined){
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

            if($revField.val() == null || $revField.val().length == 0){
                totalErrors.push({
                    $input: $revField,
                    message: "Выберите значение № ревизии",
                    row: i
                });
                showErrors();
                return;
            }


            if(listData.LASTREVISION == null || listData.LASTREVISION.length == 0){
                showErrors();
                return;
            }


            if ($revInternalField.val() != undefined && $revInternalField.val().length > 0) {
                // If revision internal has filled =>
                // a) new revInternal must be more last internal revision by 1
                // or
                // b) if lastRevInternal == null && new revision is next to last revision (A > B) && new revInternal == 1

                var lastRevisionNum;
                if(listData.LASTREVISION == null) lastRevisionNum = 0;
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


        }, function(error){
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
 * Check documents
 * @param {any} elemdsds - Wrapper jQuery element
 */
function engDocCheck(elemdsds) {

    checkEngDocs();
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
    var concatMessages = "", subMessages = "", buttons = "", buttonCancel = "<a href='#' class='button cancel'>Отмена</a>", buttonAccept = "<a href='#' class='button accept'>Выбрать</a>"

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



function SendForm(){
    var inputSysNumber = $('#_1_1_3_1');
	var typeValue = $('#_1_1_30_1').val();
	var subTypeIDValue = $('#_1_1_31_1').val();
    var direction = $('#_1_1_47_2').find("option:selected").text();
    
    removeEmptyDocsRows();
    fillDateSend();
    fixCheckboxValues();
//установка статусов (Канищев 21.06.2018)
	var workid = findGetParameter("workid");
	if(!workid && $("#_1_1_91_1_ID").length > 0 && $("#_1_1_91_1_ID").val() && $("#_1_1_91_1_ID").val().length > 0){
        //Если находимся не в задаче маршрута и поле Manager формы не пустое
        $("#_1_1_32_1").attr("value", "Согласование");

    }else if(!workid && ($("#_1_1_91_1_ID").length == 0 || $("#_1_1_91_1_ID").val().length == 0)){
        //Если находимся не в задаче маршрута и поле Manager формы – пустое
        $("#_1_1_32_1").attr("value", "Исполнено");

    }else if(workid && $("#_1_1_30_1").val() == LETTER_TYPE.OUTGOING_MEMO){
        // Если находимся в задаче маршрута 
        $("#_1_1_32_1").attr("value", "Исполнено");
    }
	
	if($("#_datepicker__1_1_95_1_99_1").val())
		if (($("#_1_1_30_1_ID").length == 0 || $("#_1_1_30_1_ID").val() == "7. Протокол") && ($("#_datepicker__1_1_95_1_99_1").val().length > 0)){
        //Если поле формы Type == 7. Протокол и есть поручение
			$("#_1_1_32_1").attr("value", "Исполнение");
		}
//Статусы установлены
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
        console.log(data)
        if (data.ok) {
            inputSysNumber.val(data.data);
      
            Click_Save(document.myForm, true);

        } else {
            alert(data.errMsg);
        }
    });
}
function Click_AnswerNotRequired() {
    applyForm('noAnswer');
}

function Click_DeliverAdministrator() {
    // applyForm('toRP');
}
function Click_GenerateXLSX (iframe, dataID, dataIDFolder) {
	//generate xlsx
	if (dataID !== undefined && dataIDFolder !== undefined ){
        console.log('RH zsabrissupd.ZEDM_GENTRANSMITTAL call')
        $.ajax({
                type:       'post',
                url:        "http://pmapp67.powerm.ru/otcs/cs.exe",
                data:       { 
                    'func'  :   'zsabrissupd.ZEDM_GENTRANSMITTAL',
                    'dataID':   dataID,
                    'dataIDFolder': dataIDFolder
                },
                success: function(data){
                    //$(data).ready()
					//$('body').append(data).ready()
					var result = data
					var el2 = document.createElement('script')
					el2.id = 'xlsxBlock';
					el2.setAttribute('id','xlsxBlock');
					var docBody = document.body;
					//docBody.append(el2)
					document.getElementById('folderBrowser').appendChild(el2);
					document.getElementById('xlsxBlock').innerHTML = 'function moveXslx(){' + result.substring(result.lastIndexOf("$(function() {") + "$(function() {".length,result.lastIndexOf("});"))  + '}';
					eval(document.getElementById('xlsxBlock').innerHTML)
					moveXslx()
					if (iframe){
						setTimeout(function() { iframe.contentWindow.location.reload(); }, 4000);
						console.log('frame reloaded');
						//document.getElementById('xlsxBlock').remove()
						$('#xlsxBlock').remove()
					}
					/*var wow = window.open()
					var texthtml = data
					$(wow.document.body).html(texthtml);
                    console.log('RH zsabrissupd.ZEDM_GENTRANSMITTAL called')
					$(wow.document.body).ready(function()
					{
						console.log('Into wow-ready');
	  					wow.close();
						//reload frame
						if (iframe){
							setTimeout(function() { iframe.contentWindow.location.reload(); }, 3000);
							console.log('frame reloaded');
						}
					});*/
                },
                error: function(xhr, status, error){
                    alert(xhr.status+", "+status+","+error);
                }   
        });
    }

    // applyForm('toExecution');
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
    });
    
}




function applyForm(sButton) {



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

    // hasError = FilterSelect.hasError();

    // if (sButton =)
    var status = $('[name=_1_1_32_1]').val();

    if (status == aTypes.isRegistration) {
        $('[name=_1_1_49_1_ID]').val(projectParams.currentUser.name);
        $('[name=_1_1_49_1_SavedName]').val(projectParams.currentUser.id);
    }

    if (sButton == 'toExecution' || sButton == 'noAnswer') {
        if ($('select#_1_1_31_1').val() == null || !$('select#_1_1_31_1').val().length) {
            LookupManager.get('ZEDM_OSG', [projectParams.ProjectCode, $('select#_1_1_31_1').val()], function (rows) {
                if (rows.length) {
                    ShowMessage('error', "Заполните поле ОСГ!");
                }
            }.bind(this));
        } else {
            if (sButton == 'toExecution') {
                $('[name=_1_1_32_1]').val(aTypes.isExecution);
            } else if (sButton == 'noAnswer') {
                $('[name=_1_1_32_1]').val(aTypes.noAnswer);
            }
			
			CheckSysNumber(Click_Save, document.myForm, true);
            //Click_Save(document.myForm, true);
        }
    } else {

        switch (sButton) {
            case 'toRP':
                if ($('[name=GetPM]').is('checked') && !!$('[name=_1_1_7_1_Name]').val().length) {
                    $('[name=_1_1_32_1]').val(aTypes.toRP);
                } else {
                    $('[name=_1_1_32_1]').val(aTypes.toRN);
                }
            break;
            case 'toRN':
                console.log('toRN')
                $('[name=_1_1_32_1]').val(aTypes.toRN);
            break;
            default:
                break;
        }

        CheckSysNumber(Click_Save, document.myForm, true);
        //Click_Save(document.myForm, true);

    }
}

function updateData(selectors, fields, callback, fieldName, value) {

    var setData = $('[' + selectors + ']');

    var getData = new Array(setData.length);

    for (i_m = 0; i_m < setData.length; i_m++) {

        var elsData = $(setData[i_m]).find('[fieldName]');
        var els = new Array(fields.length);
        for (i_c = 0; i_c < elsData.length; i_c++) {
            var fieldId = fields.indexOf($(elsData[i_c]).attr('fieldName'));
            if (fieldId >= 0)
			{
				if (els[fieldId] != null && els[fieldId].length > 0)
				{
					els[fieldId] += '; ';
				}
				else
				{
					els[fieldId] = '';
				}
				els[fieldId] += $(elsData[i_c]).val();
			}
			//if (fieldId >= 0) els[fieldId] = $(elsData[i_c]).val();
        }
		if (callback)
		{
			callback(els, fields, fieldName, value);
		}
        getData[+$(setData[i_m]).attr(selectors) - 1] = '"' + els.join('","') + '"';

    }

    return getData;
}

function DelRow(url, selectors, nickNameWR, updateEl, header, fields, delID) {

    /*if (nickNameWR == "WREngDoc" && $(".document-item").length <= 1) {
        $(".document-item input").attr("value", "");
        $(".document-item input").empty();

        $(".document-item select").attr("value", "");
        $(".document-item select").empty();

        reloadDataInDocs($(updateEl));

        return;
    }*/
	
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
    var getData = updateData(selectors, fields);
    var els = new Array(fields.length);
    getData.push('"' + els.join('","') + '"');
    var recArray = 'V{' + header + '<' + getData.join('><') + '>}';
    UpdateDataAAAA(url, nickNameWR, updateEl, recArray, callback);
}

function AddRowTask(url, selectors, nickNameWR, updateEl, header, fields, typeRow) {
    var getData = updateData(selectors, fields);
    var els = new Array(fields.length);
    els[fields.indexOf('TTYPE')] = typeRow;
    getData.push('"' + els.join('","') + '"');
    var recArray = 'V{' + header + '<' + getData.join('><') + '>}';
    UpdateDataAAAA(url, nickNameWR, updateEl, recArray, UpdateTaskDataCallback);
}

function AddRowRecipientsOut(url, selectors, nickNameWR, updateEl, header, fields, addrType) {
    var updateDataEl = function(fields, countEls, addrType) {
		var el = new Array(fields.length);
		var indexEl = fields.indexOf('ADDRTYPE');
		el[indexEl] = addrType;
		indexEl = fields.indexOf('ROWSEQNUM');
		el[indexEl] = countEls + 1;
		return el;
	}
	var getData1 = updateData(selectors + '1', fields);
	var getData2 = updateData(selectors + '2', fields);
	if (addrType == 2)
	{
		 getData2.push('"' + updateDataEl(fields, getData2.length, addrType).join('","') + '"');
	}
	var getData3 = updateData(selectors + '3', fields);
	if (addrType == 3)
	{
		 getData3.push('"' + updateDataEl(fields, getData3.length, addrType).join('","') + '"');
	}
    var recArray = 'V{' + header + '<' + getData1.concat(getData2, getData3).join('><') + '>}';
    UpdateDataAAAA(url, nickNameWR + '?volumeid=&projectcode=&outgoingform=1', updateEl, recArray);
}

function DelRowRecipientsOut(url, selectors, nickNameWR, updateEl, header, fields, delID, addrType) {

    var updateDataEl = function(el, fields, fieldName, value) {
		var indexEl = fields.indexOf(fieldName);
		if ((+el[indexEl]) > value){
			el[indexEl] = (+el[indexEl]) - 1;
		}
	}
	var getData1 = updateData(selectors + '1', fields);
	var getData2;
	var getData3;
	if (addrType == 2)
	{
		getData2 = updateData(selectors + '2', fields, updateDataEl, 'ROWSEQNUM', delID);
		getData2.splice(delID - 1, 1);
		getData3 = updateData(selectors + '3', fields);
	}
	else
	{
		getData2 = updateData(selectors + '2', fields);
		getData3 = updateData(selectors + '3', fields, updateDataEl, 'ROWSEQNUM', delID);
		getData3.splice(delID - 1, 1);
	}
    var getData = getData1.concat(getData2, getData3);
    var recArray;
    if (getData.length != 0) {
        recArray = 'V{' + header + '<' +  getData.join('><') + '>}';
    } else {
        recArray = 'V{' + header + '}';
    }


    var totalRowsOfAddrCount = $("[rowselectrecipientsout"+addrType+"]").length;
    if(totalRowsOfAddrCount == 1){

        var $wrapper = $("[rowselectrecipientsout"+addrType+"="+delID+"]");

        // Get all inputs this filled field-type
        var $inputs = $wrapper.find("[field-type]");
        $inputs.each(function(i, input){
            var $input = $(input);
            var selectize = input.selectize;
            if(selectize != undefined){
                selectize.clear(true)
            }else{
                $input.attr("value", "");
            }

        }.bind(this));
        return;
    }

    UpdateDataAAAA(url, nickNameWR + '?volumeid=&projectcode=&outgoingform=1', updateEl, recArray);
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
    
    var data = {
        DSFILETYPE: 'oscript',
        DSREQUESTDATA: recArray,
        DSHEADINGSINC: 'true'
    }

    if(updateEl == "#Tasks") data.typeDoc = "FormOut";
    

	$.ajax({
        type: "POST",
        url: url + '/open/' + nickNameWR,
        data: data,
        success: function(data){

            $(updateEl).empty().append(data);

            if (nickNameWR.indexOf("WRFormRecipientsOut") != -1) {
                showAllRows('Recipient','hiddenRow');
            }

            if (updateEl == "#Tasks") {
                reloadTasks();
            }

            if (updateEl == "#Recipients2") {
                reloadDataInDocs($(updateEl));
            }
    
            if (callback) {
                callback();
            }
        }
    });
}

/**
 * 
 * @param {{onSuccess: function(any[]), onError: function(string)}} options 
 */
function getAlbums(options){

        if(window.ALBUMS != undefined && window.ALBUMS.length > 0){
            if(options.onSuccess) options.onSuccess(window.ALBUMS);
            return;
        }

		$.ajax({
            type: "POST",
			url: projectParams.urlWL + '/open/wrGetDICAlbum',
			dataType: "json",
			data:{
				ProjectCode: projectParams.ProjectCode
			},
			async: true,
			cache: false,
			success: function(response){
                console.log(response.actualRows);

              /*  if(response.actualRows <= 0){

                    if(options.onError) options.onError("Actual rows is 0");
                    return;
                }*/


                //if(response.actualRows > 0){

                    var rows = [];
                    response.myRows.forEach(function(row){
                        var indexOfExistsRow = rows.findIndex(function(existRow){
                            return existRow.CUSTNUM == row.CUSTNUM;
                        });
    
                        var attachesIDs = (row.ATTACHDATAID != undefined) ? row.ATTACHDATAID.split("; ") : null;
                        var attachesNames = (row.ATTACHNAME != undefined) ? row.ATTACHNAME.split("; ") : null;
    
                        row.ATTACHES = (attachesIDs != undefined && attachesNames != undefined) ? attachesIDs.map(function(attachID, indexOfAttachID){
                            return {ID: parseInt(attachID), NAME: attachesNames[indexOfAttachID]}
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

                        if(indexOfExistsRow == -1){
                            indexOfExistsRow = rows.push({ CUSTNUM: row.CUSTNUM, FLDDATAID: row.FLDDATAID, LISTS: [LIST] }) - 1;
                        }else{
                            rows[indexOfExistsRow].LISTS.push(LIST);
                        }

                        rows[indexOfExistsRow].LISTS = rows[indexOfExistsRow].LISTS.filter(function(list){
                            return list.CDDATAID != undefined;
                        })
    
                    });

                    window.ALBUMS = rows;

                    if(options.onSuccess) options.onSuccess(rows);

               // }
                
               
                
				
			}
		});
}


Selectize.define('hidden_textfield', function(options) {
    var self = this;
    this.showInput = function() {
         this.$control.css({cursor: 'pointer'});
         this.$control_input.css({opacity: 0, position: 'relative', left: self.rtl ? 10000 : -10000 });
         this.isInputHidden = false;
     };

     this.setup_original = this.setup;

     this.setup = function() {
          self.setup_original();
          this.$control_input.prop("disabled","disabled");
     }
});


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
        $(newSelect).removeClass("loading-state");
/*
        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ATTACHID, [projectParams.mailFLD, projectParams.bodyID], function (rows) {

            var currentVal = $(attachSelector).val();

            $(newSelect).empty().append("<option value=''> Нет </option>");
            rows.forEach(function (row) {

                if (row.DATAID == currentVal) {
                    selectedVal = row;
                    $(newSelect).append("<OPTION SELECTED VALUE='" + row.DATAID + "' >" + row.NAME + "</OPTION>");
                    nameField.val(row.NAME);
                    nameField.removeAttr("readonly");
                } else {
                    $(newSelect).append("<OPTION VALUE='" + row.DATAID + "' >" + row.NAME + "</OPTION>");
                }

            });


            $(newSelect).removeClass("loading-state");
        });*/
    }

    var configListField = function(e, lists){

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
            onDelete: function() { return false },
            onInitialize: function () {

                if(initValue != listEl.val()){
                    this.setTextboxValue(initValue);
                    $(e).find(".list-field").addClass("errored");
                }
                return
                var $wrapper = this.$input.closest(".document-item");

                if(initValue != null){
                    var values = Object.values || valuesPolyfill;

                    var changedList = values(this.options).find(function (al) {
                        if (initValue.length == 0) {
                            return al.LIST == initValue || al.LIST == null;
                        } else {
                            return al.LIST == initValue
                        }
                    });

                    if (changedList != undefined && changedList.ATTACHES != undefined) {         
                        var attachField = $wrapper.find(".attach-field:first");

                        var currentVal = attachField.val();
                        //attachField.empty();
         
                        var optStr = changedList.ATTACHES.map(function (attach) {

                            if (attach.ID == currentVal)
                                return "<option selected value='" + attach.ID + "'>" + attach.NAME + "</option>"
                            else
                                return "<option value='" + attach.ID + "'>" + attach.NAME + "</option>"

                        }).join("")
                        console.log("optStr", optStr)
                        //attachField.append(optStr);
                        //attachName.attr("value", changedList.ATTACHES[0].NAME);
                        //attachName.removeAttr("readonly");
                        //attachField.removeAttr("readonly");
                    }
                }



            },
            onFocus: function () {
                if ($(e).find(".list-field.errored").length > 0){
                    $(e).find(".list-field.errored").removeClass("errored");
                    this.setTextboxValue("");
                }
            },
            onChange:  function (newVal) {

                if (newVal != undefined) {

                    var isInitChange = newVal.indexOf("-initChange") >= 0;
                    newVal = newVal.replace("-initChange", "");

                    console.log("isInitChange", isInitChange);
                    console.log("List field changed to", newVal);

                    $(e).find(".list-field.errored").removeClass("errored");
                    var $wrapper = this.$input.closest(".document-item");

                    var attachField = $wrapper.find(".attach-field:first");
                    var currentAttachVal = attachField.val();

                    var attachName = $wrapper.find("[fieldname=NAME]");


                    // Reset all fields
                    attachField.empty();
                    attachField.attr("readonly", true);
                    attachName.attr("value", "");
                    attachName.attr("readonly", true);

                    if (!isInitChange) {
                        
                        $wrapper.find("[mark=Revision] .valueTag").attr("value", "");
                        $wrapper.find("[mark=Revision] [name-value=REVISION]").attr("value", "");
                        $wrapper.find("[mark=RevisionInternal]").attr("value", "");
                    }
                    $wrapper.find("[mark=LastRevision]").attr("value", "");
                    $wrapper.find("[mark=LastRevisionInternal]").attr("value", "");
                    $wrapper.find("[fieldname=CDID]").attr("value", "");
                    $wrapper.find("[fieldname=MDLPERFORMER]").attr("value", "");
                    $wrapper.find("[fieldname=MDLPERFORMERFIO]").attr("value", "");
                    $wrapper.find("[fieldname=DIR]").attr("value", "");
                    $wrapper.find("[mark=Dir]").attr("value", "");

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


                if (changedList != undefined) {

                    console.log("changedList", changedList);

                    if (!isInitChange) {

                        $wrapper.find("[mark=Revision] .valueTag").attr("value", changedList.LASTREVISION);
                        $wrapper.find("[mark=Revision] [name-value=REVISION]").attr("value", changedList.LASTREVISION);
                        $wrapper.find("[mark=RevisionInternal]").attr("value", changedList.LASTREVISIONINTERNAL);

                        if (changedList.REVSTATUSCUSTOMER != undefined) $wrapper.find("[mark=RevStatusCustomer]").val(changedList.REVSTATUSCUSTOMER);
                        if (changedList.REVSTATUSINTERNAL != undefined) $wrapper.find("[mark=RevStatusInternal]").val(changedList.REVSTATUSINTERNAL);
                        if (changedList.SUBSTATUS != undefined) $wrapper.find("[mark=SubStatus]").val(changedList.SUBSTATUS);


                    }

                    $wrapper.find("[fieldname=DIR]").attr("value", changedList.DIR);
                    $wrapper.find("[mark=Dir]").attr("value", changedList.DIR);
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


                    if (changedList.ATTACHES != undefined && changedList.ATTACHES.length > 0) {
                        attachField.append(changedList.ATTACHES.map(function (attach) {
                            if (attach.ID == currentAttachVal)
                                return "<option selected value='" + attach.ID + "'>" + attach.NAME + "</option>"
                            else
                                return "<option value='" + attach.ID + "'>" + attach.NAME + "</option>"

                        }).join(""));
                        attachName.attr("value", changedList.ATTACHES[0].NAME);
                        attachName.removeAttr("readonly");
                        attachField.removeAttr("readonly");
                    }


                }

            }
          
        });

        return $sel;

    }

    var configAlbumField = function(e, albums) {

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

                if(initValue != albumEl.val()){
                    this.setTextboxValue(initValue);
                    $(e).find(".album-num").addClass("errored");
                }

                var changedAlbum = albums.find(function (al) { return al.CUSTNUM == initValue });
                $listSelect = configListField(e, (changedAlbum == undefined) ? null : changedAlbum.LISTS );
                $listSelect[0].selectize.trigger("change", $(e).find("[fieldname=LIST]").val()+"-initChange");

                console.log("Init album", changedAlbum)

                var $wrapper = this.$input.closest(".document-item");
                if(changedAlbum != undefined){
                    $wrapper.find("[fieldname=FLDID]").attr("value", changedAlbum.FLDDATAID);
                    if(changedAlbum.FLDDATAID != null  && changedAlbum.FLDDATAID > 0)  $wrapper.find(".add-filter-button:first").addClass("known");
                    else  $wrapper.find(".add-filter-button:first").removeClass("known");
                }else{
                    $wrapper.find("[fieldname=FLDID]").attr("value", "");
                    $wrapper.find(".add-filter-button:first").removeClass("known");

                }

                var listEl = $(e).find(".list-field")
                if(changedAlbum == undefined || changedAlbum.LISTS.length == 0 || changedAlbum.LISTS.length == 1){
                    listEl.addClass("myDisabled");
                }else{
                    listEl.removeClass("myDisabled");
                }



            },
            onFocus: function () {
                if ($(e).find(".album-num.errored").length > 0){
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


                if(changedAlbum == undefined || changedAlbum.FLDDATAID == null || changedAlbum.FLDDATAID == 0){
                    $wrapper.find(".add-filter-button:first").removeClass("known");
                    $wrapper.find("[fieldname=FLDID]").attr("value", "");
                }  else {
                    $wrapper.find("[fieldname=FLDID]").attr("value", changedAlbum.FLDDATAID);
                    $wrapper.find(".add-filter-button:first").addClass("known");
                } 
                 
                console.log("Album has been changed", changedAlbum);
                control.clearOptions();
                if(changedAlbum == undefined || changedAlbum.LISTS.length == 0){
                    listEl.addClass("myDisabled");
                    control.clear();
                    control.trigger("change", null);

                }else{
                    listEl.removeClass("myDisabled");
                    changedAlbum.LISTS.forEach(function(list){ control.addOption(list);});

                    if(changedAlbum.LISTS.length == 1){
                        control.setValue(changedAlbum.LISTS[0].LIST, false);
                        listEl.addClass("myDisabled");
                    }else{
                        control.clear();
                        control.trigger("change", null);
                    }
                    


                }
            }
          
        });
   


/*        if (listEl.find("[name=select]").val() == undefined || listEl.find("[name=select]").val().length == 0) {
            listEl.find("[name=select]").addClass("loading-state");
        }


        var filter = new FilterSelect({
            id: $(albumEl).attr("id"),
            onData: function (sValue, onData) {
                if (sValue != undefined && sValue.length > 0) {
                    this.getData({
                        type: 'GET',
                        url: '',
                        data: {
                            func: 'webform.securelookup',
                            lookupname: 'ZEDM_ENGDOCNUM',
                            P1: projectParams.ProjectCode,
                            PT1: 'string',
                            P2: sValue,
                            PT2: 'string'
                        },
                        ondata: function (data) {

                            data = data.substring(1, data.length - 8);
                            var obj = $.parseJSON(data);

                            if (!obj.error) {
                                var aData = [];
                                obj.rows.forEach(function (e) {
                                    for (k in e) {
                                        e[k] = decodeURIComponent(e[k]);
                                    }
                                    aData.push(e);
                                });
                                onData(aData);
                            }
                        }
                    })
                }


            },
            showValList: 'CUSTNUM',
            onComplite: function (sValue) {
                $(e).find(".list-field:first input").val("");
                $(e).find(".list-field:first input").addClass("loading-state");
                $(e).find(".list-field:first ul").empty();



                $(e).find("[mark=LastRevision]").val("");
                $(e).find("[mark=LastRevisionInternal]").val("");
                $(e).find("[mark=LastRevStatusInternal]").val("");
                $(e).find("[mark=LastRevStatusCustomer]").val("");
                $(e).find("[mark=LastSubStatus]").val("");


                if (sValue != undefined && sValue.length > 0) {
                    var listid = $(e).find(".list-field:first").attr("id");
                    loadOptions("DocsList", [sValue, listid]);
                }
            }
        })*/
    }

    var configRevField = function (e) {

        LookupManager.get(LookupManager.FUNCTIONS.ZEDM_ENGDOCREVSTATUS, [projectParams.ProjectCode], function (rows) {
            var revStatusCustomer = $(e).find("[mark=RevStatusCustomer]");
            var currentVal = revStatusCustomer.val();
            revStatusCustomer.empty();

            var revStatusInternal = $(e).find("[mark=RevStatusInternal]");
            var currentValInternal = revStatusInternal.val();
            revStatusInternal.empty();

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

    var addFolder = function(){
        var $this = $(this);
        var $wrapper = $this.closest(".document-item");
        var fldID = $wrapper.find("[fieldname=FLDID]").val();

        if(fldID != undefined && fldID.length > 0){
            var url = projectParams.urlWL + "/Open/" + fldID + "/?NEXTURL="+encodeURI(location.protocol + '//' + location.host + location.pathname);
            window.open(url)
        }else{

            ShowIlyasMessage("System", [
                '<select class="system-list" id="systemList" disabled><option disabled value selected>Загрузка списка</option></select>', 
            ].join(""), null, function(modalWindow){


                var value =  $(modalWindow).find("#systemList").val();
                console.log("Selected value", value)

                if(value == undefined || value.length == 0) return

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




            }, function(modalWindow){
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
        onSuccess: function(rows){
            docsEl.find(".document-item").each(function (i, e) {
                configAttachField(e);
                configAlbumField(e, rows);
                configRevField(e);
                $(e).find(".add-filter-button").click(addFolder);
        
                $(e).find(".row-id").empty().append((i + 1));

            });
        },
        onError: function(error){
            docsEl.find(".document-item").each(function (i, e) {
                configAttachField(e);
                configAlbumField(e, []);
                configRevField(e);
                $(e).find(".row-id").empty().append((i + 1));
            });
        }
    })

    
}

var valuesPolyfill = function values(object) {
    return Object.keys(object).map(function(key){ return object[key]; });
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
      value: function(predicate) {
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
	AddAttr: function (idAttr, callback)
	{
		var elSource = $(idAttr);
		var elParent = $(elSource).parent();
		var elNew = $(idAttr).clone();
		var inputs = $(elNew).find(':input');
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].selectedIndex != null){
				inputs[i].selectedIndex  = 0;
			}
			else
			{
				$(inputs[i]).val('');
            }
		}
		
		var name = (elSource[0].name != null && elSource[0].name.length > 0) ? elSource[0].name : elSource[0].id;
		if (name != null && name.length > 0)
		{
			var prefix = '_1_1_' + $(elNew).attr('prefixrow');
			var reg = new RegExp(prefix + "\\d+");
			name = name.replace(new RegExp(reg), prefix + '0');
			elNew[0].name = elNew[0].id = name;
		}
		$(elNew).find('#delRow').css('display', 'inline');
		
		$(elSource).after(elNew);
		var newElId = this.RenameAttrs(elParent, name);
		if (callback){
			callback(elNew);
        }
        if($(elNew).attr('prefixrow') == "47_") dirNameUpdated();

		
	},
	DelAttr: function (idAttr, callback)
	{
		var elParent = $(idAttr).parent();
		$(idAttr).remove();
		var blocks = $(elParent).find('[prefixrow]');
		if (blocks.length == 1)
		{
			$(blocks[0]).find('#delRow').css('display', 'none');
		}
		
		this.RenameAttrs(elParent);
		if (callback){
			callback(idAttr);
        }		
        if($(elNew).attr('prefixrow') == "47_") dirNameUpdated();


	},
	RenameAttrs: function (parentElement, tempId)
	{
		var newElId;
		var blocks = $(parentElement).find('[prefixrow]');
		for (var i_b = 0; i_b < blocks.length; i_b++)
		{
			var prefix = '_1_1_' + $(blocks[i_b]).attr('prefixrow');
			var inputs = $(blocks[i_b]).find('*');
			var reg = new RegExp(prefix + "\\d+");
			var name = (blocks[i_b].name != null && blocks[i_b].name.length > 0) ? blocks[i_b].name : blocks[i_b].id;
			if (name != null && name.length > 0)
			{
				name = name.replace(new RegExp(reg), prefix + (i_b + 1));
				if (blocks[i_b].id == tempId)
				{
					newElId = name;
				}
				blocks[i_b].name = blocks[i_b].id = name;
			}

			for (var i_i = 0; i_i < inputs.length; i_i++)
			{
				name = (inputs[i_i].name != null && inputs[i_i].name.length > 0) ? inputs[i_i].name : inputs[i_i].id;
				if (name == null || name.length == 0)
				{
					continue;
				}
				name = name.replace(new RegExp(reg), prefix + (i_b + 1));
				if (name == null || name.length == 0)
				{
					continue;
				}
				inputs[i_i].name = inputs[i_i].id = name;
			}
			if (blocks.length > 1)
			{
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


function dirNameUpdated(){

    console.log("dirNameUpdated");

    $("#_1_1_48_1").attr("value", $(".DirName").length);

  /*  $("#GetPM").prop('checked', $(".DirName").length != 1);
    markDirty($("#GetPM").get());

    if($(".DirName").length != 1){
        $("#_1_1_82_1").attr("value", "");
        $("#_1_1_82_1").attr("title", "");
        $("[name=_1_1_82_1_Name]").attr("value", "");
        $("[name=_1_1_82_1_ID]").attr("value", "");
    }else{
        loadOptions("DM", [$(".DirName:first select").val()]);
    }*/
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

function isFunction(functionToCheck) {
    return functionToCheck && {}.toString.call(functionToCheck) === '[object Function]';
}


function checkRegNumExist(onSuccess, onError){
    var workid = findGetParameter("workid");
    //Если находимся не в задаче маршрута и поле Manager формы не пустое
    if(!workid && $("#_1_1_91_1_ID").length > 0 && $("#_1_1_91_1_ID").val() && $("#_1_1_91_1_ID").val().length > 0){
        onSuccess();
        return;
    }

    var regNum = $("#_1_1_4_1").val();
    if(!regNum || regNum.length == 0){
        onError("Поле 'Регистрационный номер' не заполнено");
        return;
    }

    if(!workid){
        workid = projectParams.dataIdForm;
    }


    LookupManager.get(LookupManager.FUNCTIONS.ZEDM_CHECKREGNUM, [projectParams.ProjectCode, regNum, workid], function (rows) {

        if(!rows[0] || !rows[0].STATUS){
            onSuccess();
            return;
        }
        
        ShowIlyasMessage("regEdit", [
            'Документ с таким Регистрационным № уже существует, статус документа "'+rows[0].STATUS+'"',
        ].join(""), null, function (modalWindow) {
            // On register
            console.log("On register");
            onSuccess();
        }, null, function(modalWindow){
            // On edit
            console.log("On edit");

            onError("HIDDEN");
        });


       
    }, onError);
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

function CheckSysNumber(metodSave, param1, param2) {
	var inputSysNumber = $('#_1_1_3_1');
	var valueSysNumber = inputSysNumber.val();
	if (valueSysNumber != null && valueSysNumber.length > 0) {
		metodSave(param1, param2);
	}
	var typeValue = $('#_1_1_30_1').val();
	var subTypeIDValue = $('#_1_1_31_1').val();
	var direction = $('#_1_1_47_2').find("option:selected").text();
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
			metodSave(param1, param2);
        } else {
            alert(data.errMsg);
        }
    });
}


Array.prototype.groupBy = function(keyFunction) {
    var groups = {};
    this.forEach(function(el) {
        var key = keyFunction(el);
        if (key in groups == false) {
            groups[key] = [];
        }
        groups[key].push(el);
    });
    return Object.keys(groups).map(function(key) {
        return {
            key: key,
            values: groups[key]
        };
    });
};




/**
 * Show modal window
 * @param {string} sType Type of message
 * @param {string} sMessage Text or html string
 * @param {string} [aSubMessage] Subtext (optional)
 * @param {function(jQuery)} cb Callback for accept button. It returns modal window object
 */
function ShowIlyasMessage(sType, sMessage, aSubMessage, cb, configBlock) {
    var concatMessages = "", buttons = "", buttonCancel = "<a href='javascript:void(0);' class='button cancel'>Отмена</a>", buttonAccept = "<a href='javascript:void(0);' class='button accept'>Выбрать</a>"

    switch (sType) {
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
        }else if($(event.target).hasClass('cancel')){
            toogleLoader({show: false});
        }
        this.domModalWindow.remove();
        this.overlay.remove();
    }.bind({
        domModalWindow: domModalWindow,
        overlay: overlay
    }));

    if(configBlock != undefined){
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


var makeURL = function(funcName, params){
    var url =  projectParams.urlWL + "?func="+funcName;
    var optionsStr = Object.keys(params).reduce(function(prev, current){
        return prev + "&" + current + "=" + params[current];
    }, "");
    return url+optionsStr;
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
        var answerVal = $("#_1_1_39_1").val();
        if ((taskDateVal == undefined || taskDateVal.length == 0) && (answerVal != undefined && answerVal.length > 0)) {
            $(oTaskEl.find("[fieldname=TASKDUEDATE]")).attr("value", answerVal);
            fillDate($(oTaskEl.find("[fieldname=TASKDUEDATE]")).attr("name"));
        }
        fillDate($(oTaskEl.find("[fieldname=TASKDUEDATE]")).attr("name"));


        // Config TaskDueDate
        //oTaskEl.find(".task-datepicker:first").removeAttr("onchange");
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
            fieldname: "TASKTYPE",
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





window.ElementDatePicker = {
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

