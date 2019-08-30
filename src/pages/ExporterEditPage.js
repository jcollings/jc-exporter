import React from 'react';
import ExporterForm from '../components/ExporterForm';
import PropTypes from 'prop-types';
import Header from '../components/Header';
import Errors from '../components/Errors';
import Loading from '../components/Loading';
import {exporter} from '../services/exporter.service';
import {withRouter} from 'react-router';

const AJAX_BASE  = window.wpApiSettings.admin_base;

class ExporterEditPage extends React.Component {

    constructor(props){
        super(props);
        this.state = {
            loaded: true,
            saved: true,
            errors: [],
            exporter: { id: null, name: '', type: '', fields: [], file_type: ''},
            active: 'exporters'
        };

        this.handleSubmit = this.handleSubmit.bind(this);
        this.getExporter = this.getExporter.bind(this);
        this.handleChange = this.handleChange.bind(this);
    }

    getExporter(id) {

        this.setState({loaded: false});

        exporter.get(id).then(data => {
            this.setState({exporter: data});
        }).catch(error => {
            this.setState({
                errors: [...this.state.errors, {
                    section: 'archive',
                    message: error
                }]
            });
        }).finally(() => {
            this.setState({loaded: true});
        });
    }

    handleSubmit(){

        return new Promise((resolve, reject) => {
            this.setState({saved: false});

            exporter.save(this.state.exporter).then(data => {

                if (this.state.exporter.id === null){
                    // eslint-disable-next-line react/prop-types
                    this.props.history.push(AJAX_BASE + '&edit=' + data.id);
                }

                this.setState({exporter: data});
                resolve();
            }).catch(error => {
                this.setState({
                    errors: [...this.state.errors, {
                        section: 'archive',
                        message: error
                    }]
                });
                reject();
            }).finally(() => {
                this.setState({saved: true});
            });
        });
    }

    handleChange(event) {

        const target = event.target;
        let value = target.value;

        if (target.type === 'select-multiple') {

            value = [];
            for (let i = 0; i < target.options.length; i++) {
                if (target.options[i].selected) {
                    value.push(target.options[i].value);
                }
            }
        }

        const exporter = this.state.exporter;
        exporter[target.name] = value;

        if (target.name === 'type'){
            exporter['fields'] = [];
        }

        this.setState({
            exporter: exporter
        });
    }

    componentDidMount() {

        if (this.props.id > 0){
            this.getExporter(this.props.id);
        } else {
            this.setState({active: 'new'});
        }
    }

    componentDidUpdate(prevProps) {

        const id = parseInt(this.props.id) || 0;
        const prevId = parseInt(prevProps.id) || 0;
        if (id !== prevId){
            if (id > 0){
                this.getExporter(id);
                this.setState({active: 'exporters'});
            } else {
                this.setState({exporter: { id: null, name: '', type: '', fields: []}});
                this.setState({active: 'new'});
            }
        }
    }

    render() {

        return (
            <React.Fragment>
                <div className="ewp-exporter-edit">

                    <Header active={this.state.active}/>
                    <hr className="wp-header-end" />

                    <div className="ewp-body">

                    <Errors section="archive" errors={this.state.errors}/>
                    <Loading loading={!this.state.loaded}/>
                    <Loading loading={!this.state.saved} type="success" message="Saving"/>

                    {this.state.loaded &&
                        <ExporterForm onFormSubmit={this.handleSubmit} onInputChanged={this.handleChange}
                                  exporter={this.state.exporter} onRun={this.props.onRun}/>
                    }
                    </div>
                </div>
            </React.Fragment>
        );
    }
}

ExporterEditPage.propTypes = {
    id: PropTypes.number,
    onRun: PropTypes.func.isRequired
};

export default withRouter(ExporterEditPage);