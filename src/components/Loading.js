import React from 'react';
import PropTypes from 'prop-types';

const Loading = ({loading, type, message}) => (
    <React.Fragment>
        {loading &&
        <div className="ewp-notices">
            <div className={'ewp-notice ewp-notice--' + type}>
                <p>{message}</p>
            </div>
        </div>
        }
    </React.Fragment>
);

Loading.defaultProps = {
    type: 'info',
    message: 'Loading'
};

Loading.propTypes = {
    loading: PropTypes.bool.isRequired,
    type: PropTypes.string,
    message: PropTypes.string
};

export default Loading;