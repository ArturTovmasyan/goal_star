/**
 * Created by pc-4 on 11/4/15.
 */
var params = require('./parameters.json');

params.rest.hostname            = "luvbyrd.laravelsoft.com";
params.rest.userIdpath          = "/api/v1.0/message/user/id";
params.rest.statusPath          = "/api/v1.0/users/{user}/statuses/{status}";
params.rest.emailNotification   = "/api/v1.0/messages/{userId}/email";
params.rest.pushNotification   = "/api/v1.0/messages/{userId}/push";

params.socket.secure            = true;
params.ssl.cert                 = "/etc/ssl/certs/luvbyrd.crt";
params.ssl.key                  = "/etc/ssl/private/luvbyrd.key";
params.ssl.ca                   = "";

module.exports = params;
