/**
 * Created by aram on 7/27/16.
 */

var Imagemin = require('imagemin');
var cliargs = require('cliargs');
var fs = require('fs');
var path = require('path');
var gm = require('gm');

var argsObj = cliargs.parse();

if(!argsObj.f || !argsObj.p || !argsObj.r || !argsObj.t){
    console.log('invalid arguments');
    return;
}

if(argsObj.p === 'jpg' || argsObj.p === 'jpeg' || argsObj.p === 'JPG'){
    gm(argsObj.f)
        .write(argsObj.r + argsObj.t, function (err) {
            if (err){
                console.log(err);
                return;
            }
            console.log('ok');
        });
    return;
}

var imagemin = new Imagemin()
    .src(argsObj.f)
    .dest(argsObj.r);


switch(argsObj.p){
    case 'png':
        imagemin.use(Imagemin.pngquant());
        break;
    case 'gif':
        imagemin.use(Imagemin.gifsicle({ interlaced: true }));
        break;
    case 'svg':
        imagemin.use(Imagemin.svgo());
}

imagemin.run(function (err) {
    if (err) {
        console.log(err);
        return;
    }
    console.log('ok');
});