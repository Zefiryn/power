/**
 * HAT custom file
 *
 * @param program Main "program" object (https://github.com/tj/commander.js)
 * @param {String} lwd - HAT library root folder
 */
module.exports = function (program, lwd) {
    var cm = require(lwd + '/common'),
        eventEmitter = require(lwd + '/api/event'),
        logger = require(lwd + '/api/logger');

    async function afterInstallDependencies(data) {
        const container = data.container
        const path = `${container.containerWebRootProjectDir}`
        container.exec(`cd ${path} && node node_modules/.bin/encore prod`, { silent: false })

    }

    eventEmitter.onEvent('after:container-created', function (globalEventData) {
        globalEventData.container.onEvent('after:install-dependencies', afterInstallDependencies)
    });
}
