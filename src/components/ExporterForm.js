import React from 'react';
import PropTypes from 'prop-types';
import {Link} from 'react-router-dom';
import {exporter} from '../services/exporter.service';
import Loading from './Loading';

const AJAX_BASE  = window.wpApiSettings.admin_base;

export default class ExporterForm extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            errors: [],
            loaded: false,
            ran: true,
            ranProgress: ''
        };

        this.run = this.run.bind(this);
        this.save = this.save.bind(this);
    }

    run(event){

        event.preventDefault();

        this.props.onFormSubmit().then(() => {
            this.setState({ran: false});
            const observable = exporter.run(this.props.exporter.id);
            observable.subscribe(
                response => {

                    if (response.hasOwnProperty('file')){
                        window.location.href = response.file;
                        this.setState({ran: true});
                    } else {
                        console.log('progress', response);
                        this.setState({ranProgress: response.progress.toFixed() + '%'});
                    }
                },
                error => {
                    console.log('error', error);
                },
                () => {
                    console.log('complete');
                    this.setState({ran: true});
                }
            );
        });
    }

    save(event){
        event.preventDefault();
        this.props.onFormSubmit();
    }

    render() {

        const post_fields = ['ID', 'post_name', 'post_excerpt', 'post_content', 'post_thumbnail', 'post_author', 'ewp_tax_post_tag', 'ewp_tax_category', 'ewp_cf__thumbnail_id'];

        const typeOptions = [
            {id: 'user', label: 'Users', fields: ['ID', 'user_login', 'user_pass']},
            {id: 'post', label: 'Posts', fields: post_fields},
            {id: 'page', label: 'Pages', fields: post_fields},
        ];

        const fileTypes = [
            {id: 'csv', label: 'CSV file'},
            {id: 'xml', label: 'XML file'}
        ];

        const {name, type, fields, file_type} = this.props.exporter;

        const activeType = typeOptions.find(option => option.id === type);

        return (
            <React.Fragment>
                <Loading loading={!this.state.ran} type="success" message={'Running ' + this.state.ranProgress}/>
                <div className="ewp-form">
                    <form onSubmit={this.save}>

                        <div className="ewp-form__row">
                            <label className="ewp-form__label">Name:</label>
                            <input className="ewp-form__input" type="text" name="name" value={name}
                                   onChange={this.props.onInputChanged} required={true}/>
                        </div>

                        <div className="ewp-form__row">
                            <label className="ewp-form__label">Exporting:</label>
                            <select className="ewp-form__input" name="type"
                                    onChange={this.props.onInputChanged} value={type}>
                                <option value="">Choose option</option>
                                {typeOptions.map(option => (
                                    <option key={option.id} value={option.id}>{option.label}</option>
                                ))}
                            </select>
                        </div>

                        {typeof activeType !== 'undefined' &&
                            <div className="ewp-form__row">
                                <label className="ewp-form__label">Fields to export:</label>
                                <select className="ewp-form__input" name="fields" multiple="multiple"
                                        onChange={this.props.onInputChanged} value={fields}>
                                    {typeOptions.find(option => option.id === type).fields.map(option => (
                                        <option key={option} value={option}>{option}</option>
                                    ))}
                                </select>
                            </div>
                        }

                        <div className="ewp-form__row">
                            <label htmlFor="" className="ewp-form__label">File Type:</label>
                            <select name="file_type" id="" className="ewp-form__input" onChange={this.props.onInputChanged} value={file_type}>
                                <option value="">Choose option</option>
                                {fileTypes.map(option => (
                                    <option key={option.id} value={option.id}>{option.label}</option>
                                ))}
                            </select>
                        </div>

                        <div className="form__row">
                            <div className="ewp-buttons">
                            <button className="button button-primary" type="submit">Save</button>
                                <button type="button" className="button button-secondary" onClick={this.run}>Test Run</button>
                                {this.props.exporter.id > 0 &&
                                <Link to={AJAX_BASE + '&run=' + this.props.exporter.id}
                                      className="button button-secondary">Run</Link>
                                }
                            </div>
                        </div>
                    </form>
                </div>
            </React.Fragment>
        );
    }
}

ExporterForm.defaultProps = {
    exporter: {
        id: null,
        name: '',
        type: '',
        fields: []
    }
};

ExporterForm.propTypes = {
    onFormSubmit: PropTypes.func.isRequired,
    onInputChanged: PropTypes.func.isRequired,
    exporter: PropTypes.object
};