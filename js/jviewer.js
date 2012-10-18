/*jslint  browser: true, indent: 4, plusplus: true, eqeq: true*/
/** COPYRIGHT Time at Task */
JSViewer = function () {

    "use strict";

    // Globals
    var my_key_codes, my_image_count, current_image_index,
        $, renderImage, showPrevImage, showNextImage,
        keyDownHandler, keyUpHandler, keyPressHandler, setKeyboardHandlers, isShiftPressed,isDeletePressed,
        toggleArrows, addArrows,
        loadImage, images,
        cacheGroup, log,
        // // START : image details
        image_details, image_errors, general_error, displayFlashError,
        restrict_db_update, onDbUpdateFailure,
        loadImageDetail, populateImageDetail, saveImageDetail,markImageDeleted;
        // // END : image details

    images = {};
    current_image_index = 0; // keep track of the current image being viewed

    // // START : image details
    image_details = [];
    image_errors = [];
    restrict_db_update = [];
    // // END : image details

    /**
     * Creates a shortcut for document.getElementById
     *
     * @param {id} the did of the element we want to get
     * @return {html object} The html object element
     */
    $ = function (id) {
        return document.getElementById(id);
    };

    /**
     * Renders an image on the screen
     *
     * @param {Y} Yui3 object
     * @param {total_number_images} the total number of images
     * @return {null}
     */
    renderImage = function (Y, image_data) {
        var image_detail, pos_top;

        if (!image_data.src) {
            throw "Invalid argument";
        }

        /*
        This sets the image src to the image data and reposition the image arrows,
        then focus on the account field
        */
        $('jsv_image').src = 'loading.gif';
        $('jsv_image').src = image_data.src;
        $('jsv_image').style.maxWidth = "100%";
        $('jsv_image').style.maxHeight = "100%";
        $('jsv_enclosure_number').value = String(current_image_index + 1);
        $('jsv_account').focus();
        $('jsv_link').href = image_data.src;

        // // START : image details
        // var image_detail = image_details[current_image_index];
        image_detail = image_details[current_image_index + 1];
        populateImageDetail(Y, image_detail);
        // // END : image details

        pos_top = Y.one('#jsv_image').get('height') - 200;

        pos_top += 'px';
        Y.one('#arrow_left').setStyle('bottom', pos_top);
        Y.one('#arrow_right').setStyle('bottom', pos_top);
    };

    // // START : image details
    /**
     * Loads image detail from the server
     *
     * @param {Y} Yui3 object
     * @param {imageID} the ID of the image to load
     * @return {null}
     */
    loadImageDetail = function (Y, imageID) {
        // Skip loading from database if:
        // 1) a save to db is in progress
        if (restrict_db_update[imageID]) {
            log('skipping database loading of image id ' + imageID);
            return;
        }

        // 2) image has validation errors
        if (image_errors[imageID]) {
            log('skipping database loading of image id ' + imageID);
            return;
        }

        // 3) image does not exists i.e. pressing 'q' at the first image or 'w' at the last image
        if (imageID < 0 || imageID > my_image_count) {
            log('skipping database loading of image id ' + imageID);
            return;
        }

        Y.io("php/proxy.php?function=get_image_detail", {
            method: 'POST',
            data : "image_id=" +  imageID,
            // // ajax lifecycle event handlers
            on: {
                complete: function (id, response) {
                    // var data = response.responseText; // Response data.
                    var jsonObject = null;

                    try {
                        jsonObject = Y.JSON.parse(response.responseText);
                        image_details[imageID] = jsonObject;
                    } catch (e) {
                        log("Failed to load image details");
                        log(e);
                        log(response);
                    }

                    // // Update fields too if the image is currently on display
                    if ((imageID == current_image_index + 1) && (jsonObject !== null)) {
                        populateImageDetail(Y, jsonObject);
                    }
                }
            }
        });
    };

    /**
     * Populates image detail onto form
     *
     * @param {Y} Yui3 object
     * @param {obj} Object with image details as properties
     * @return {null}
     */
    populateImageDetail = function (Y, obj) {
        var image_id, errors;
        if (obj) {
            $('jsv_date').value = obj.date;
            $('jsv_text').value = obj.text;
            $('jsv_amount').value = obj.amount;
            $('jsv_account').value = obj.account;
            $('jsv_vat_code').value = obj.vat_code;
            $('jsv_offset_account').value = obj.offset_account;
            Y.one('#jsv_image_name').setHTML(obj.image_name);
            if (!obj.deleted) {
          	  	Y.one('#image_deleted_info').setHTML('');
            } else {
            	Y.one('#image_deleted_info').setHTML('Deleted');
            }
        }

        image_id = current_image_index + 1;
        if (image_errors[image_id]) {
            // Populate errors next to their respective fields
            errors = image_errors[image_id];

            Y.one('#error_date').setHTML(errors.date);
            Y.one('#error_text').setHTML(errors.text);
            Y.one('#error_amount').setHTML(errors.amount);
            Y.one('#error_account').setHTML(errors.account);
            Y.one('#error_vat_code').setHTML(errors.vat_code);
            Y.one('#error_offset_account').setHTML(errors.offset_account);

            // // The next one is a general error not related to any field
            Y.one('#error_general').setHTML(errors.general);
        } else {
            // Clear field errors from previous image
            Y.all('span.field_error').setHTML('');
        }
    };

    /**
     * Saves image detail to server
     *
     * @param {Y} Yui3 object
     * @param {current_image_index} The index of the current displayed image
     * @return {null}
     */
    saveImageDetail = function (Y, current_image_index) {
        var ddate, image_id, text, amount, account, vat_code, offset_account, obj;

        // Skip saving to database if:
        // 1) image does not exists i.e. pressing 'w' at the last image
        if (current_image_index == my_image_count) {
            return;
        }

        image_id = current_image_index + 1;

        // Get field values for obj
        // var date = $('jsv_date').value;
        ddate = $('jsv_date').value;
        text = $('jsv_text').value;
        amount = $('jsv_amount').value;
        account = $('jsv_account').value;
        vat_code = $('jsv_vat_code').value;
        offset_account = $('jsv_offset_account').value;
        // Get obj in RAM
        obj = image_details[image_id];

        // Abort silently if there is no obj
        if (!obj) {
            return;
        }

        // If there is a difference between the object in RAM
        // and the field values, save object.
        // NOTE : "undefined" is not the same as an empty string :)
        if (obj.date == ddate && obj.text == text && obj.amount == amount && obj.account == account && obj.offset_account == offset_account && obj.vat_code == vat_code) {

            log('no need to save ' + image_id);

        } else {

            // Update object in RAM
            obj.date = ddate;
            obj.text = text;
            obj.amount = amount;
            obj.account = account;
            obj.vat_code = vat_code;
            obj.offset_account = offset_account;
            image_details[image_id] = obj;

            // Subscribe function 'onDbUpdateFailure' to "io.failure" i.e. timeout,
            // passing it Y & the affected image id when that happens
            Y.on('io:failure', onDbUpdateFailure, Y,
                // 'Transaction Failed'
                [Y, image_id]
            );

            Y.io("php/proxy.php?function=set_image_detail", {
                // // this is a post
                method: 'POST',
                // // serialize the form. keeps bugging out ...
                // form: {
                //    id: imageID,
                // },
                data : "image_id=" +  image_id
                    // + "&date=" + date
                    + "&date=" + ddate
                    + "&text=" + text
                    + "&amount=" + amount
                    + "&account=" + account
                    + "&vat_code=" + vat_code
                    + "&offset_account=" + offset_account,
                // Abort the transaction, if it is still pending, after 3000ms.
                timeout : 3000,
                // Ajax lifecycle event handlers
                on: {
                    start: function (id) {
                        log('saving ' + image_id + ' ...');

                        // Prevent other threads from overwriting the user's
                        // typed in values while the request is in progress.
                        restrict_db_update[image_id] = true;
                    },
                    complete: function (id, response) {
                       var jsonObject = Y.JSON.parse(response.responseText);

                        log(image_id + ' saved');
                        log(jsonObject);

                        if (jsonObject.status == 0) {
                            image_errors[image_id] = jsonObject.errors;
                        }

                        if (jsonObject.status == 1) {
                            /*
                            // Update object in RAM
                            obj['date'] = ddate;
                            obj['text'] = text;
                            obj['amount'] = amount;
                            obj['account'] = account;
                            obj['offset_account'] = offset_account;
                            image_details[image_id] = obj;

                            // // Update fields too if the image is currently on display
                            if (image_id == current_image_index + 1) {
                                populateImageDetail(Y, obj);
                            }
                            */

                            // Remove its errors
                            delete image_errors[image_id];

                            // Remove update restriction
                            delete restrict_db_update[image_id];
                       }

                        // // Clear the general error as we can connect
                        // // to the server just fine
                        general_error = '';

                        // // Update the flash_errors div to notify
                        // // users that there is/are error(s)
                        displayFlashError(Y);
                    }
                }
            });

        }
    };
    
    /**
     * Requset to mark image as deleted on database
     *
     * @param {Y} Yui3 object
     * @param {current_image_index} The index of the current displayed image
     * @return {null}
     */
    markImageDeleted = function (Y, current_image_index) {
        var image_id,obj, deleted;
        if(!isDeletePressed) return;
        // Skip saving to database if:
        // 1) image does not exists i.e. pressing 'w' at the last image
        if (current_image_index == my_image_count) {
            return;
        }

        image_id = current_image_index + 1;

        // Update object in RAM
        obj = image_details[image_id];
        // image alredy marked as deleted so return;
        if(obj.deleted) {
        	deleted=false;
        } else deleted=true;
        // Update object in RAM
        obj.deleted=deleted;
        image_details[image_id] = obj;

        // Subscribe function 'onDbUpdateFailure' to "io.failure" i.e. timeout,
        // passing it Y & the affected image id when that happens
        Y.on('io:failure', onDbUpdateFailure, Y,
            // 'Transaction Failed'
            [Y, image_id]
        );

        Y.io("php/proxy.php?function=mark_image_as_deleted", {
            // // this is a post
            method: 'POST',
            data : "image_id=" +  image_id + "&deleted=" + deleted,
            timeout : 3000,
            // Ajax lifecycle event handlers
            on: {
                start: function (id) {
                	if(!deleted) log('mark image as undeleted ' + image_id + ' ...');
                	else  log('mark image as deleted ' + image_id + ' ...');
                    

                    // Prevent other threads from overwriting the user's
                    // typed in values while the request is in progress.
                    restrict_db_update[image_id] = true;
                },
                complete: function (id, response) {
                   var jsonObject = Y.JSON.parse(response.responseText);
                    if(!deleted) {
                    	log(image_id + ' marked as undeleted'); 
                    	Y.one('#image_deleted_info').setHTML('');
                    }
                    else {
                    	log(image_id + ' marked as deleted'); 
                    	Y.one('#image_deleted_info').setHTML('Deleted');
                    }
                    log(jsonObject);

                    if (jsonObject.status == 0) {
                        image_errors[image_id] = jsonObject.errors;
                    }

                    if (jsonObject.status == 1) {
                        // Remove its errors
                        delete image_errors[image_id];

                        // Remove update restriction
                        delete restrict_db_update[image_id];
                   }

                    // // Clear the general error as we can connect
                    // // to the server just fine
                    general_error = '';

                    // // Update the flash_errors div to notify
                    // // users that there is/are error(s)
                    displayFlashError(Y);
                }
            }
        });
    };

    /**
     * Handles storage of errors from timeouts arising from example
     * when the server is unreachable when saving an image's detail.
     *
     * The customer for this project actually has a test case, where he
     * disconnects his PC from LAN and expects the app to inform him
     * that the database update has failed.
     *
     * @param {transactionid} The transaction's ID
     * @param {response} The response object.  Only status and statusText
     *                  are populated when the transaction is terminated
     *                  due to abort or timeout. The status will read 0,
     *                  and statusText will return "timeout" or "abort"
     *                  depending on the mode of termination.
     * @param {argzz} Can be anything you want. Here it is the id
     *                  of the image which we were trying to update
     * @return {null}
     */
    onDbUpdateFailure = function (transactionid, response, argzz) {
        // image_errors[argzz[1]] = {general : 'Unable to connect to database'};
        general_error = 'Unable to connect to database';

        // // Since the 'complete' event of the Ajax handler wasn't
        // // reached if there is a timeout, run the code to display
        // // errors here as well
        displayFlashError(argzz[0]);
    };

    /**
     * Iterates through all entries in image_errors and checks if
     * it contains valid errors i.e. not empty
     *
     * If any is found, then the flash error message is shown.
     *
     * @param {Y} Yui3 object
     * @return {null}
     */
    displayFlashError = function(Y) {
        var errorCount = 0, i = image_errors.length;
        while (i--) {
            if (typeof image_errors[i] !== 'undefined')
                errorCount++;
        }
        var errorString = '';
        if (errorCount > 0) {
            errorString = errorCount + (errorCount > 1 ? ' errors ' : ' error ') + 'found';
        }
        if (general_error != '') {
            errorString = general_error + '<br/>' + errorString;
        }
        Y.one('#flash_errors').setHTML(errorString);
    };
    // // END : image details

    log = function (m) {
        if (window.console) {
            window.console.log(m);
        }
    };

    /**
     * Renders the previous image on the screen
     *
     * @param {Y} Yui3 object
     * @param {total_number_images} the total number of images
     * @param {POST_CACHE} this is the number of images to render before we start caching again
     * @param {PRE_CACHE} this is the number of images to cached at a time
     * @return {null}
     */
    showPrevImage = function (Y, total_number_images, POST_CACHE, PRE_CACHE) {
        return function (e) {
            if (e) {
                e.stopPropagation();
            }

            // // START : image details
            saveImageDetail(Y, current_image_index);
            // // END : image details

            if (current_image_index > 0) {
                current_image_index--;
            }

            log('showPrevImage : current_image_index = ' + current_image_index);

            if (!images[current_image_index]) {
                cacheGroup(Y, current_image_index, PRE_CACHE);
            }

            try {
                renderImage(Y, images[current_image_index]);
            } catch (e) {
                log(e);
            }
        };
    };

    /**
     * Renders the next image on the screen
     *
     * @param {Y} Yui3 object
     * @param {total_number_images} the total number of images
     * @param {POST_CACHE} this is the number of images to render before we start caching again
     * @param {PRE_CACHE} this is the number of images to cached at a time
     * @return {null}
     */
    showNextImage = function (Y, total_number_images, POST_CACHE, PRE_CACHE) {
        return function (e) {
            if (e) {
                e.stopPropagation();
            }

            // // START : image details
            saveImageDetail(Y, current_image_index);
            // // END : image details

            if ((current_image_index + 1) < total_number_images) {
                current_image_index++;
            }

            log('showNextImage : current_image_index = ' + current_image_index);

            renderImage(Y, images[current_image_index]);

            cacheGroup(Y, current_image_index, PRE_CACHE);
        };
    };

    /**
     * Handles a keydown press
     *
     * @param {Y} Yui3 object
     * @param {total_number_images} the total number of images
     * @param {POST_CACHE} this is the number of images to render before we start caching again
     * @param {PRE_CACHE} this is the number of images to cached at a time
     * @return {null}
     */
    keyDownHandler = function (Y, total_number_images, POST_CACHE, PRE_CACHE) {
        return function (e) {
            if(document.activeElement.id == "jsv_text") return;
            var rows = document.getElementById(window.accountsTable.get('id')).children[0].children[2]
                .children[1].children[0].getElementsByTagName("tr");

            document.newHotkey = -1;

            if (e.target.getAttribute('class') == 'hotkey-input' ) {
                if ( document.reservedKeys.indexOf(e.keyCode) != -1 || e.keyCode == 87 || e.keyCode == 81 ) {
                        document.getElementById(e.target.getAttribute('id')).parentNode.children[1].innerHTML = "This key can't be assigned because it is a reserved key!";
                        e.preventDefault();
                        return false;
                }

                for (var i = 1; i < rows.length; i++) {
                    if ( e.keyCode == rows[i].children[3].children[0].getAttribute('data')
                         && e.target.getAttribute('id') != rows[i].children[3].children[0].getAttribute('id') ) {
                        document.getElementById(e.target.getAttribute('id')).value = " ";
                        var name = rows[i].children[1].innerHTML;
                        document.getElementById(e.target.getAttribute('id')).parentNode.children[1].innerHTML = "This key can't be assigned because it is assigned to '" + name + "'!";
                        e.preventDefault();
                        document.newHotkey = -1;
                        return false;
                    } else if (e.target.getAttribute('id') == rows[i].children[3].children[0].getAttribute('id')) {
                        document.newHotkey = i;
                    }
                }
                document.getElementById(e.target.getAttribute('id')).setAttribute('data', e.keyCode);
                document.getElementById(e.target.getAttribute('id')).parentNode.children[1].innerHTML = "";
                document.getElementById(e.target.getAttribute('id')).value = "";
                return true;
            }

            var a = Array();

            document.hotkey = false;
            for (var i = 1; i < rows.length; i++) {
                if ( e.keyCode ==  rows[i].children[3].children[0].getAttribute('data') ) {
                    document.hotkey = true;
                    document.getElementById('jsv_account').value = rows[i].children[0].innerHTML;
                    document.getElementById('account_name').innerHTML = rows[i].children[1].innerHTML;
                    if(!isShiftPressed){
                    	document.getElementById('jsv_vat_code').value = rows[i].children[2].innerHTML;
                    } else {
                        //check if vat_code is this same that bind on hotkey
                    	// if this same set empty value
                    	if(rows[i].children[2].innerHTML.replace(/^\s+|\s+$/g,"") == document.getElementById('jsv_vat_code').value.replace(/^\s+|\s+$/g,"")) {
                       		document.getElementById('jsv_vat_code').value ="";
                       	}
                    }
                    break;
                }
            }
            switch (e.keyCode) {
            case 13: // enter
            case 87: // w (next)
                showNextImage(Y, total_number_images, POST_CACHE, PRE_CACHE)(e);
                document.hotkey = true;
                break;
            case 81: //q (previous)
                showPrevImage(Y, total_number_images, POST_CACHE, PRE_CACHE)(e);
                document.hotkey = true;
                break; 
            case 16:// shift (if pressed then hotkey will clear vat_code field)
            	isShiftPressed=true;
            	break;
            case 46: //delete (previous)
            	if(!isDeletePressed) {
            		// set flag to prevent multi key down event (and multirequest to database) if hold delete key
            		isDeletePressed=true;
	                markImageDeleted(Y, current_image_index);
            	}
                break; 
            }
        };
    };

    /**
     * Handles a keyup event
     *
     * @param {Y} Yui3 object
     * @param {total_number_images} the total number of images
     * @param {POST_CACHE} this is the number of images to render before we start caching again
     * @param {PRE_CACHE} this is the number of images to cached at a time
     * @return {null}
     */
    keyUpHandler = function (Y, total_number_images, POST_CACHE, PRE_CACHE) {
        return function (e) {
            if(document.activeElement.id == "jsv_text") return;
            var rows = document.getElementById(window.accountsTable.get('id')).children[0].children[2]
                .children[1].children[0].getElementsByTagName("tr");

            if(e.target.getAttribute('class') == 'hotkey-input') {
                if (document.newHotkey != -1) {
                    rows[document.newHotkey].children[3].children[0].blur();
                }
                return false;
            }

            var a = Array();
            for (var i = 1; i < rows.length; i++) {
                if ( e.keyCode ==  rows[i].children[3].children[0].getAttribute('data') ) {
                    document.getElementById('account_name').innerHTML = "";
                    showNextImage(Y, total_number_images, POST_CACHE, PRE_CACHE)(e);
                    // set isShiftPressed false (hotkey will not clear field)
                    isShiftPressed=false;
                    break;
                }
            }
            // set flag isDeletePressed to false 
            if ( e.keyCode == 46) {
            	isDeletePressed=false;
            	showNextImage(Y, total_number_images, POST_CACHE, PRE_CACHE)(e);
            }
        };
    };

    /**
     * Handles a keypress event
     *
     * The keypress event is trigged between keydown and keyup and is the . If document.hotkey flag is true,
     * this mean that a hoykey was pressed and the keyPress even need to avoid write the character on the current
     * input because the keycode was consumed by keyDown event.
     *
     * @return {null}
     */
    keyPressHandler = function() {
        return function (e) {
            if (document.hotkey == true) {
                e.preventDefault();
                document.hotkey = false;
                return false;
            }
        }
    };

    /**
     * Initialize the keyboard handlers
     *
     * @param {Y} Yui3 object
     * @param {total_number_images} the total number of images
     * @param {POST_CACHE} this is the number of images to render before we start caching again
     * @param {PRE_CACHE} this is the number of images to cached at a time
     * @return {null}
     */
    setKeyboardHandlers = function (Y, total_number_images, POST_CACHE, PRE_CACHE) {
        Y.one('doc').on("keydown", keyDownHandler(Y, total_number_images, POST_CACHE, PRE_CACHE), 'enter,81,87');
        Y.one('doc').on("keyup", keyUpHandler(Y, total_number_images, POST_CACHE, PRE_CACHE));
        Y.one('doc').on("keypress", keyPressHandler());
    };

    /**
     * Handles hiding and showing of arrows
     *
     * @param {Y} Yui3 object
     * @param {show} if true, show the arrow, otherwise, hide them
     * @return {null}
     */
    toggleArrows = function (Y, show) {
        return function (e) {
            Y.one('#arrow_left').setStyle('display', (show ? 'inline' : 'none'));
            Y.one('#arrow_right').setStyle('display', (show ? 'inline' : 'none'));
        };
    };

    /**
     * Add navigation arrows to the image
     *
     * @param {Y} Yui3 object
     * @param {POST_CACHE} this is the number of images to render before we start caching again
     * @param {PRE_CACHE} this is the number of images to cached at a time
     * @return {null}
     */
    addArrows = function (Y, total_number_images, POST_CACHE, PRE_CACHE) {
        var pos_top, arrowLeft, arrowRight;

        pos_top = Y.one('#jsv_image').get('height') - 200;

        pos_top += 'px';

        arrowLeft = $a({
            'id': 'arrow_left',
            'href': '#',
            'style': 'display:none;text-decoration: none !important; font-weight: bold; font-size: 70px; color: #CC0000;position:absolute;bottom:' + pos_top + ';left:0;z-index:9999999'
        }, '<');

        arrowRight = $a({
            'id': 'arrow_right',
            'href': '#',
            'style': 'display:none;text-decoration: none; font-weight: bold; font-size: 70px; color: #CC0000;position:absolute;bottom:' + pos_top + ';z-index:999999'
        }, '>');

        $('jsv_link').appendChild(arrowLeft);
        $('jsv_link').appendChild(arrowRight);

        Y.one('#jsv_link').on('mouseover', toggleArrows(Y, true));
        Y.one('#jsv_link').on('mouseout', toggleArrows(Y, false));

        Y.one(arrowRight).on('click', showNextImage(Y, total_number_images, POST_CACHE, PRE_CACHE));
        Y.one(arrowLeft).on('click', showPrevImage(Y, total_number_images, POST_CACHE, PRE_CACHE));

    };

    /**
     * Use ajax to get the image data
     *
     * @param {Y} Yui3 object
     * @param {imageID} the id of image we want to get
     * @param {total_number_images} the total number of images
     * @param {POST_CACHE} this is the number of images to render before we start caching again
     * @param {PRE_CACHE} this is the number of images to cached at a time
     * @return {null}
     */
    loadImage = function (Y, imageID) {
        var i = new Image();
        i.src = "?imageID=" + imageID + '&data=1&imageonly=1';

        images[imageID] = i;

        // // START : image details
        // // Perhaps we should get the image binary and details in one HTTP request ?
        loadImageDetail(Y, imageID);
        // // END : image details
    };

    /**
     * Use ajax to get the image data
     *
     * @param {Y} Yui3 object
     * @param {from} position to start getting images from
     * @param {total_number_images} the total number of images
     * @param {POST_CACHE} this is the number of images to render before we start caching again
     * @param {PRE_CACHE} this is the number of images to cached at a time
     * @return {null}
     */
    cacheGroup = function (Y, from, PRE_CACHE) {
        var i = 0, n;

        if (from > 0) {
            loadImage(Y, from - 1);
            for (n = from - 2; n > 0; n--) {
              images[n] = null;
            }
        }
        for (i = 0; i < PRE_CACHE; i++) {
            loadImage(Y, from + i);
        }
    };

    return {

        /**
         * Initializes the Yui3 object and gets things rolling,.
         *
         * @param {Y} Yui3 object
         * @param {from} position to start getting images from
         * @param {total_number_images} the total number of images
         * @param {POST_CACHE} this is the number of images to render before we start caching again
         * @param {PRE_CACHE} this is the number of images to cached at a time
         * @return {null}
         */
        start : function (total_number_images, POST_CACHE, PRE_CACHE, from, key_codes) {

            my_image_count = total_number_images;
            my_key_codes = key_codes;

            YUI().use("io", "dump", "json-parse", 'node', 'event', 'transition', 'node-load', 'anim',  function (Y) {
                /*
                    We cache PRE_CACHE number of images at a time, starting from the first image
                */
                current_image_index = from;
                $('jsv_enclosure_number').value = String(current_image_index + 1);

                cacheGroup(Y, current_image_index, PRE_CACHE);

                var imageID = current_image_index;

                if (!$('jsv_image')) {
                    var image_link, first_image;
                    image_link = $a({
                        'style': 'position:relative;text-decoration:none;',
                        'id': 'jsv_link',
                        'href': '?imageID=' + imageID
                    });

                    first_image = $img({
                        'id': 'jsv_image',
                        'src': '?imageID=' + from + '&data=1&imageonly=1'
                    });

                    image_link.appendChild(first_image);

                    $('jsv_left').appendChild(image_link);

                    addArrows(Y, total_number_images, POST_CACHE, PRE_CACHE);
                    setKeyboardHandlers(Y, total_number_images, POST_CACHE, PRE_CACHE);
                }

                $('log').innerHTML = "";

                renderImage(Y, images[imageID]);
            });



        },
        debug: function () {
            log(images);
            log(current_image_index);
        }
    };

}();
