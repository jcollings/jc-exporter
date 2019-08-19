import React from 'react';
import PropTypes from 'prop-types';

export default class Errors extends React.Component {
    render() {

        const {errors} = this.props;

        return (
            <React.Fragment>
                {errors.length > 0 &&
                    <div className="ewp-notices">
                        {errors.filter(error => error.section === this.props.section).map((error, i) => (
                            <div key={i} className="ewp-notice ewp-notice--error">
                                <p>{error.message}</p>
                            </div>
                        ))}
                    </div>
                }
            </React.Fragment>
        );
    }
}

Errors.propTypes = {
    errors: PropTypes.array.isRequired,
    section: PropTypes.string.isRequired
};