import React from 'react';
import PropTypes from 'prop-types';
import {Link} from 'react-router-dom';

const AJAX_BASE  = window.wpApiSettings.admin_base;

export default class ExporterListItem extends React.Component {
    render() {

        const {id, name, type, file_type} = this.props.exporter;

        return (
            <div className="ewp-exporter-list__item">
                <h1>{name}</h1>
                <p>Export <strong>{type}</strong> to <strong>{file_type}</strong> file.</p>
                <div className="ewp-buttons">
                <Link to={AJAX_BASE+'&edit=' + id } className="button button-secondary button-small">Edit</Link>
                <Link to={AJAX_BASE+'&run=' + id } className="button button-primary button-small">Run</Link>
                </div>
            </div>
        );
    }
}

ExporterListItem.propTypes = {
    exporter: PropTypes.object.isRequired,
};