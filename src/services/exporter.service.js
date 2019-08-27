import {Observable} from 'rxjs';

const AJAX_BASE = window.wpApiSettings.ajax_base;
let current_xhr = null;

export const exporter = {
    run,
    get,
    save,
    abort,
    remove
};

function abort(){
    if (current_xhr !== null){
        current_xhr.abort();
    }
}

function run(id) {

    abort();

    return new Observable(subscriber => {

        let jsonResponse = '', lastResponseLen = false;

        current_xhr = window.jQuery.ajax({
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

                    const parts = thisResponse.split('\n');
                    if (parts.length > 0) {
                        parts.map(part => {
                            if (part.length > 0) {
                                subscriber.next(JSON.parse(part.replace('\n','')));
                            }
                        });
                    } else {
                        jsonResponse = JSON.parse(thisResponse.replace('\n',''));
                        subscriber.next(jsonResponse);
                    }
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
        current_xhr = window.jQuery.ajax({
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
        current_xhr = window.jQuery.ajax({
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

function remove(id){
    return new Promise((resolve, reject) => {
        const url = AJAX_BASE + '/exporter/' + id;
        current_xhr = window.jQuery.ajax({
            url: url,
            dataType: 'json',
            method: 'DELETE',
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