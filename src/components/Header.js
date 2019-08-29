import React from 'react';
import PropTypes from 'prop-types';
import {Link} from 'react-router-dom';

const ADMIN_BASE = window.wpApiSettings.admin_base;

export default class Header extends React.Component {
    render() {

        const { active} = this.props;

        return (
            <div className="ewp-header">
                <h1 className="nav-tab-wrapper">
                    <span className="wp-heading-inline ewp-logo">ExportWP</span>
                    <Link to={ADMIN_BASE + '&edit'}  className="page-title-action ewp-page-title-action">Add
                        New</Link>

                    <span className="ewp-nav-tab-right">
                        <Link to={ADMIN_BASE}  className={'nav-tab' + (active === 'exporters' ? ' nav-tab-active' : '')}>Exporters</Link>
                        {/* <Link to={ADMIN_BASE+'&tab=settings'} className={'nav-tab' + (active === 'settings' ? ' nav-tab-active' : '')}>Settings</Link> */}
                    </span>
                </h1>
            </div>
        );
    }
}

Header.propTypes = {
    active: PropTypes.string
};