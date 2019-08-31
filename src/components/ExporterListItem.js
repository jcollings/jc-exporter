import React from 'react';
import PropTypes from 'prop-types';
import {Link} from 'react-router-dom';

const AJAX_BASE  = window.wpApiSettings.admin_base;

export default class ExporterListItem extends React.Component {
    render() {

        const {id, name, type, file_type} = this.props.exporter;

        return (
            <div className="ewp-exporter-list__item">
                <div className="ewp-item">
                    <div className="ewp-item__left">
                        <h2>{name}</h2>
                        <p>Export <strong>{type}</strong> to <strong>{file_type}</strong> file.</p>
                    </div>
                    <div className="ewp-item__right">
                        <div className="ewp-buttons">
                            <Link to={AJAX_BASE+'&edit=' + id } className="button button-secondary button-small">Edit</Link>
                            <a onClick={() => { this.props.onRun(this.props.exporter); }} className="button button-primary button-small">Export</a>
                            <a onClick={() => { this.props.onDelete(this.props.exporter); }} className="button button-link-delete button-small">Delete</a>
                        </div>
                    </div>
                </div>
                <div className="ewp-item__progress">
                    <p>Last Exported on 31/08/2019</p>
                </div>
            </div>
        );
    }
}

ExporterListItem.propTypes = {
    exporter: PropTypes.object.isRequired,
    onRun: PropTypes.func.isRequired,
    onDelete: PropTypes.func.isRequired
};