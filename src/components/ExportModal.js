import React from 'react';
import PropTypes from 'prop-types';
import {exporter} from '../services/exporter.service';

const AJAX_BASE = window.wpApiSettings.ajax_base;

export default class ExportModal extends React.Component {

    constructor(props){
        super(props);

        this.state = {
            ran: false,
            progress: 0,
            exporter: { id: null, name: '', type: '', fields: [], file_type: ''}
        };

        this.run = this.run.bind(this);
        this.close = this.close.bind(this);
    }


    run(currentExporter){

        this.setState({
            ran: false,
            progress: 0,
            exporter: currentExporter
        });

        const observable = exporter.run(currentExporter.id);
        observable.subscribe(
            response => {
                if (response.hasOwnProperty('file')){
                    window.location.href = AJAX_BASE + '/exporter/' + currentExporter.id + '/download/' + response.file;
                    this.setState({
                        ran: true,
                        progress: 100
                    });
                } else {
                    this.setState({progress: response.progress.toFixed()});
                }
            },
            error => {
                console.log('error', error);
            },
            () => {
                this.setState({
                    ran: true,
                    progress: 100
                });
            }
        );
    }

    close(){
        exporter.abort();
        this.setState({exporter: { id: null, name: '', type: '', fields: [], file_type: ''}});
    }

    render() {
        const {type, file_type} = this.state.exporter;
        return (
            <div className="ewp-modal" style={{display: this.state.exporter.id !== null ? 'block' : 'none'}}>
                <div className="ewp-modal-backdrop" onClick={this.close}/>
                <div className="ewp-modal-wrapper">
                    <div className="ewp-modal-inside">
                        <span className="ewp-modal-close" onClick={this.close}>x</span>
                        <h2 className="ewp-modal__title">Exporter Progress ({this.state.progress}%)</h2>
                        <p>Exporting <strong>{type}</strong> to <strong>{file_type}</strong> file.</p>
                        <progress className="ewp-progress-bar" max={100} value={this.state.progress}/>
                    </div>
                </div>
            </div>
        );
    }
}

ExportModal.propTypes = {
};