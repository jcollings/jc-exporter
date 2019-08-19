import React from 'react';
import ExporterListItem from '../components/ExporterListItem';
import {Link} from 'react-router-dom';
import Errors from '../components/Errors';
import Header from '../components/Header';
import Loading from "../components/Loading";

const AJAX_BASE = window.wpApiSettings.wprb_ajax_base;

export default class ExporterArchivePage extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            loaded: false,
            exporters: [],
            errors: []
        };

        this.xhr = null;
        this.getExporters = this.getExporters.bind(this);
    }


    getExporters() {
        this.xhr = window.jQuery.ajax({
            url: AJAX_BASE + '/exporters',
            dataType: 'json',
            method: 'GET',
            beforeSend: function(xhr) {
                this.setState({loaded: false});
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiSettings.nonce);
            }.bind(this),
            success: function(data) {
                this.setState({exporters: data});
            }.bind(this),
            error: function(data) {

                if (data.statusText === 'abort'){
                    return;
                }

                this.setState({
                    errors: [...this.state.errors, {
                        section: 'archive',
                        message: data.responseJSON.message
                    }]
                });
            }.bind(this),
            complete: function() {
                this.setState({loaded: true});
            }.bind(this)
        });
    }

    componentDidMount() {
        this.getExporters();
    }

    componentWillUnmount() {
        if (this.xhr !== null) {
            this.xhr.abort();
        }
    }

    render() {

        return (
            <div className="ewp-exporter-archive">

                <Header active="exporters"/>

                <hr className="wp-header-end"/>

                <Errors section="archive" errors={this.state.errors}/>
                <Loading loading={!this.state.loaded} />

                <div className="ewp-exporter-list">
                    {this.state.exporters.length === 0 && this.state.loaded &&
                    <p>No Exporters Found</p>
                    }
                    {this.state.exporters.map(exporter => (
                        <ExporterListItem key={exporter.id} exporter={exporter}/>
                    ))}
                </div>
            </div>
        );
    }
}