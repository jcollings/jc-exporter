import React from 'react';
import './App.scss';
import ExporterArchivePage from './pages/ExporterArchivePage';
import ExporterEditPage from './pages/ExporterEditPage';
import qs from 'qs';
import SettingsPage from './pages/SettingsPage';

export default class App extends React.Component {

    render() {

        const values = qs.parse(this.props.location.search);

        if ( typeof values.edit !== 'undefined' ) {
            const id = parseInt(values.edit);
            return <ExporterEditPage id={id} />;
        }

        if ( typeof values.tab !== 'undefined'){
            const tab = values.tab;
            if (tab === 'settings'){
                return <SettingsPage />;
            }
        }

        return <ExporterArchivePage/>;
    }
}