import React from 'react';
import ExporterListItem from '../components/ExporterListItem';
import Errors from '../components/Errors';
import Header from '../components/Header';
import Loading from '../components/Loading';
import PropTypes from 'prop-types';
import {exporter} from '../services/exporter.service';

export default class ExporterArchivePage extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            loaded: false,
            exporters: [],
            errors: [],
            exporter: { id: null, name: '', type: '', fields: [], file_type: ''},
            openModal: false,
            status: []
        };

        this.exportersXHR = null;
        this.statusXHR = null;


        this.getExporters = this.getExporters.bind(this);
        this.getStatus = this.getStatus.bind(this);
        this.handleDelete = this.handleDelete.bind(this);
    }


    getExporters() {

        this.exportersXHR = exporter.exporters();

        this.exportersXHR.request.then( data => {

            this.setState({
                exporters: data,
                loaded: true
            });
        }).catch( data => {

            if (data.statusText === 'abort'){
                return;
            }

            this.setState({
                errors: [...this.state.errors, {
                    section: 'archive',
                    message: data.responseJSON.message
                }],
                loaded: true
            });

        });
    }

    getStatus(){
        this.statusXHR = exporter.status();
        this.statusXHR.request.subscribe(
            response => {
                this.setState({status: response});
            }
        );
    }

    handleDelete(data){
        this.setState({loaded: false});
        this.setState({exporters: this.state.exporters.filter(item => item.id !== data.id)});
        exporter.remove(data.id).finally(() => {
            this.getExporters();
        });
    }

    componentDidMount() {
        this.getExporters();
        this.getStatus();
    }

    componentWillUnmount() {
        this.exportersXHR.abort();
        this.statusXHR.abort();
    }

    render() {

        return (
            <React.Fragment>
                <div className="ewp-exporter-archive">

                    <Header active="exporters"/>

                    <div className="ewp-body">

                        <Errors section="archive" errors={this.state.errors}/>
                        <Loading loading={!this.state.loaded} />

                        <div className="ewp-exporter-list">
                            {this.state.exporters.length === 0 && this.state.loaded &&
                            <p>No Exporters Found</p>
                            }
                            {this.state.exporters.map(exporter => (
                                <ExporterListItem key={exporter.id} exporter={exporter} status={this.state.status.find( item => item.id === exporter.id)} onRun={this.props.onRun} onDelete={this.handleDelete}/>
                            ))}
                        </div>
                    </div>
                </div>
            </React.Fragment>
        );
    }
}

ExporterArchivePage.propTypes = {
    onRun: PropTypes.func.isRequired
};