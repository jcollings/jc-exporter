import React from 'react';
import './App.scss';
import ExporterArchivePage from './pages/ExporterArchivePage';
import ExporterEditPage from './pages/ExporterEditPage';
import qs from 'qs';
import SettingsPage from './pages/SettingsPage';
import ExportModal from './components/ExportModal';

export default class App extends React.Component {

    constructor(props) {
        super(props);

        this.runner = React.createRef();
        this.handleRun = this.handleRun.bind(this);
    }

    handleRun(exporter = null){
        this.runner.current.run(exporter);
    }

    render() {

        const values = qs.parse(this.props.location.search);
        let page = '';

        if ( typeof values.edit !== 'undefined' ) {
            page = <ExporterEditPage id={parseInt(values.edit)} onRun={this.handleRun} />;
        } else if ( typeof values.tab !== 'undefined' && values.tab === 'settings'){
            page = <SettingsPage />;
        } else {
            page = <ExporterArchivePage onRun={this.handleRun}/>;
        }

        return (
            <React.Fragment>
                <ExportModal ref={this.runner} />
                {page}
            </React.Fragment>
        );
    }
}