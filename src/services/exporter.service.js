import {Observable} from 'rxjs';

const AJAX_BASE = window.wpApiSettings.ewp_ajax_base;
let xhr = null;

export const exporter = {
    run,
    get,
    save,
    abort
};

function abort(){
    if(xhr !== null){
        xhr.abort();
    }
}

function run(id) {

    abort();

    return new Observable(subscriber => {

        let jsonResponse = '', lastResponseLen = false;

        xhr = window.jQuery.ajax({
            url: AJAX_BASE + '/exporter/' + id + '/run',
            dataType: 'json',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiSettings.nonce);
            },
            xhrFields: {
                onprogress: function(e) {
                    var thisResponse, response = e.currentTarget.response;
                    if (lastResponseLen === false) {
                        thisResponse = response;
                        lastResponseLen = response.length;
                    } else {
                        thisResponse = response.substring(lastResponseLen);
                        lastResponseLen = response.length;
                    }

                    jsonResponse = JSON.parse(thisResponse);
                    subscriber.next(jsonResponse);
                }
            },
            complete: function() {
                subscriber.complete();
            }
        });
    });
}

function get(id) {

    abort();

    return new Promise((resolve, reject) => {
        xhr = window.jQuery.ajax({
            url: AJAX_BASE + '/exporter/' + id,
            dataType: 'json',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiSettings.nonce);
            },
            success: function(data) {
                resolve(data);
            },
            error: function(data) {
                reject(data.responseJSON.message);
            }
        });
    });
}

function save(data) {

    abort();

    return new Promise((resolve, reject) => {
        const url = data.id > 0 ? AJAX_BASE + '/exporter/' + data.id : AJAX_BASE + '/exporter';
        xhr = window.jQuery.ajax({
            url: url,
            dataType: 'json',
            method: 'POST',
            data: data,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiSettings.nonce);
            },
            success: resolve,
            error: function(data) {
                reject(data.responseJSON.message);
            }
        });
    });
}