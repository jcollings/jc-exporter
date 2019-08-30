import React from 'react';
import PropTypes from 'prop-types';
import {Link} from 'react-router-dom';

const ADMIN_BASE = window.wpApiSettings.admin_base;

export default class Header extends React.Component {
    render() {

        const { active} = this.props;

        return (
            <div className="ewp-header">
                <h1>
                    ExportWP
                </h1>

                <div className="ewp-header__tabs">
                    <Link to={ADMIN_BASE + '&edit'} className={'ewp-header__tab' + (active === 'new' ? ' ewp-header__tab--active' : '') }>Add New</Link>
                    <Link to={ADMIN_BASE} className={'ewp-header__tab' + (active === 'exporters' ? ' ewp-header__tab--active' : '') }>Exporters</Link>
                </div>
            </div>
        );
    }
}

Header.propTypes = {
    active: PropTypes.string
};