/**
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on
 * an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations under the License.
 *
 * @file src/bce_base_client.js
 * @author leeight
 */

/* eslint-env node */

var EventEmitter = require('../vendor/events').EventEmitter;
var util = require('../vendor/util');
var Q = require('../vendor/q');
var u = require('../vendor/underscore');
var config = require('./config');
var Auth = require('./auth');

/**
 * BceBaseClient
 *
 * @constructor
 * @param {Object} clientConfig The bce client configuration.
 * @param {string} serviceId The service id.
 * @param {boolean=} regionSupported The service supported region or not.
 */
function BceBaseClient(clientConfig, serviceId, regionSupported) {
    EventEmitter.call(this);

    this.config = u.extend({}, config.DEFAULT_CONFIG, clientConfig);
    this.serviceId = serviceId;
    this.regionSupported = !!regionSupported;

    this.config.endpoint = this._computeEndpoint();

    /**
     * @type {HttpClient}
     */
    this._httpAgent = null;
}
util.inherits(BceBaseClient, EventEmitter);

BceBaseClient.prototype._computeEndpoint = function () {
    if (this.config.endpoint) {
        return this.config.endpoint;
    }

    if (this.regionSupported) {
        return util.format('%s://%s.%s.%s',
            this.config.protocol,
            this.serviceId,
            this.config.region,
            config.DEFAULT_SERVICE_DOMAIN);
    }
    return util.format('%s://%s.%s',
        this.config.protocol,
        this.serviceId,
        config.DEFAULT_SERVICE_DOMAIN);
};

BceBaseClient.prototype.createSignature = function (credentials, httpMethod, path, params, headers) {
    return Q.fcall(function () {
        var auth = new Auth(credentials.ak, credentials.sk);
        return auth.generateAuthorization(httpMethod, path, params, headers);
    });
};

module.exports = BceBaseClient;

