/**
 * Created by andranik on 6/3/15.
 */

var Sequelize = require('sequelize');
var YAML = require('yamljs');

result = YAML.load('../../../../app/config/parameters.yml');
result = result.parameters;

sequelize = new Sequelize(
    result.database_name,
    result.database_user,
    result.database_password,
    {
        host: result.database_host,
        logging: false
    }
);

module.exports = sequelize;