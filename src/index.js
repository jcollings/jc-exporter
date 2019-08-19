import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';
import {BrowserRouter as Router, Route} from 'react-router-dom';

const routes = (
    <div className="wrap">
        <Router>
            <Route path="/" component={App}/>
        </Router>
    </div>
);

ReactDOM.render((routes), document.getElementById('jc-exporter-root'));