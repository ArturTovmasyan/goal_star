/**
 * Created by pc-4 on 11/3/15.
 */
var http    = require('http');
var https   = require('https');
var params  = require('./config/parameters');
var fs      = require('fs');

// Default secure SSL is true
var protocol = params.socket.secure ? https : http;

var app      = null;
if(params.socket.secure){
    var options = {
        key: fs.readFileSync(params.ssl.key),
        cert: fs.readFileSync(params.ssl.cert),
        rejectUnauthorized: params.socket.rejectUnauthorized
    };

    if(options.rejectUnauthorized){
        options.ca = [fs.readFileSync(params.ssl.ca)];
    }
    else {
        process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";
    }

    app = https.createServer(options, function(req, res) {});
}
else {
    app = http.createServer(function(req, res) {});
}

var io      = require('socket.io')(app);
var User    = require('./lib/models').User;
var Message = require('./lib/models').Message;
var dFormat = require('dateformat');

app.listen(params.socket.port);

var onlineUsers = {};
var isMessageNotificationSend = {};

io.on('connection',function(socket){

    // getting the PHPSESSION
    socket.on('PHPSESSION',function(PHPSESSION, apikey){


        var options = {
            hostname: params.rest.hostname,
            path: params.rest.userIdPath,
            headers: {
                'Cookie': 'PHPSESSID=' + PHPSESSION,
                'apikey': apikey ? apikey : null
            }
        };

        protocol.get(options, function(res) {
            res.on('data', function (d) {

                var user;
                try {
                    user = JSON.parse(d.toString());
                }
                catch (e){
                    return socket.disconnect();
                }

                if (isNaN(parseFloat(user.id)) || !user.first_name || !user.message_image) {
                    socket.disconnect();
                    return;
                }

                socket.join(user.id);
                socket.userId = user.id;
                socket.user = user;
                socket.PHPSESSION = PHPSESSION;
                socket.apikey = apikey;

                onlineUsers[user.id] = {
                    user: user,
                    PHPSESSION: PHPSESSION,
                    apikey: apikey,
                    socket: socket
                };

                console.log(user);
            });
        });
    });

    socket.on('readMessage',function(data){
        if (parseInt(socket.userId) == data.userId){
            return;
        }

        var where = {
            is_read: false,
            from_user_id: data.userId,
            to_user_id: socket.userId
        };

        Message.update({is_read: true},{where: where}).then(function(count){
            io.to(socket.userId).emit('message_count', -1 * count);
        })
    });

    // getting messages
    socket.on('message', function(data) {
        if (socket.userId == data.userId){
            return;
        }

        if(isNaN(parseFloat(data.userId)) || !isFinite(data.userId)){
            return;
        }

        var userId        = data.userId;
        var subject       = data.subject;
        var content       = data.content;

        User.find({where: {id: userId}}).then(function(user) {
            if (user){
                var message = {
                    from_user_id: socket.userId,
                    to_user_id: userId,
                    subject: subject,
                    content: content,
                    is_read: false,
                    is_deleted: false,
                    created: dFormat(new Date(), 'isoDateTime')
                };

                Message.create(message).then(function() {});
                message.from_user = socket.user;
                io.to(user.id).emit('message', message);
                io.to(user.id).emit('message_count', 1);
                io.to(socket.userId).emit('message', message);

                sendPushNotification(socket.PHPSESSION, user.id, socket.apikey);

                if(!onlineUsers[user.id] && !isMessageNotificationSend[user.id]){
                    sendEmailNotification(socket.PHPSESSION, user.id, socket.apikey)
                }
            }
        })
    });

    // we use this for like, unlike and report
    socket.on('status', function(data) {
        if (socket.userId == data.userId){
            return;
        }

        var statusPath = params.rest.statusPath.replace('{user}', data.userId).replace('{status}', data.status);

        var options = {
            hostname: params.rest.hostname,
            path: statusPath,
            headers: {
                'Cookie': 'PHPSESSID=' + socket.PHPSESSION,
                'apikey': socket.apikey ? socket.apikey : null
            }
        };

        protocol.get(options, function(res) {
            if (res.statusCode == 200){
                io.to(data.userId).emit('status', {
                    fromUserId: socket.userId,
                    toUserId:   data.userId,
                    status:     data.status
                });
            }
        });
    });

    // when user disconnects
    socket.on('disconnect', function(){
        onlineUsers[socket.userId] = null;
        isMessageNotificationSend[socket.userId] = false;
        console.log('disconnect...');
    })

});

function sendEmailNotification(phpS, userId, apikey){
    var options = {
        hostname: params.rest.hostname,
        path: params.rest.emailNotification.replace('{userId}', userId),
        headers: {
            'Cookie': 'PHPSESSID=' + phpS,
            'apikey': apikey ? apikey : null
        }
    };

    protocol.get(options, function(res){
        if(res.statusCode == 200) {
            isMessageNotificationSend[userId] = true;
        }
    });
}

function sendPushNotification(phpS, userId, apikey){
    var options = {
        hostname: params.rest.hostname,
        path: params.rest.pushNotification.replace('{userId}', userId),
        headers: {
            'Cookie': 'PHPSESSID=' + phpS,
            'apikey': apikey ? apikey : null
        }
    };

    protocol.get(options, function(res){
        // if(res.statusCode == 200) {
        //     isMessageNotificationSend[userId] = true;
        // }
    });
}