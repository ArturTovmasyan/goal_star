'use strict';

angular.module('Smiley', ['emoji'])
    .constant('SmileyResourcePath', '/app/bower_components/angular-emoji-filter/res/emoji/')
    .value('SmileyItems', {
        angel:  {icon: 'emoji_angel.png', code: ':angel:'},
        anger:  {icon: 'emoji_anger.png', code: ':anger:'},
        angry:  {icon: 'emoji_angry.png', code: ':angry:'},
        art:    {icon: 'emoji_art.png', code: ':angry:'},
        bear:   {icon: 'emoji_bear.png', code: ':bear:'},
        beer:   {icon: 'emoji_beer.png', code: ':beer:'},
        blush:  {icon: 'emoji_blush.png', code: ':blush:'},
        books:  {icon: 'emoji_books.png', code: ':books:'},
        bread:  {icon: 'emoji_bread.png', code: ':bread:'},
        bus:    {icon: 'emoji_bus.png', code: ':bus:'},
        cake:   {icon: 'emoji_cake.png', code: ':cake:'},
        car:    {icon: 'emoji_car.png', code: ':car:'},
        cat2:   {icon: 'emoji_cat2.png', code: ':cat2:'},
        clap:   {icon: 'emoji_clap.png', code: ':clap:'},
        cloud:  {icon: 'emoji_cloud.png', code: ':cloud:'},
        cop:    {icon: 'emoji_cop.png', code: ':cop:'},
        cow:    {icon: 'emoji_cow.png', code: ':cow:'},
        cry:    {icon: 'emoji_cry.png', code: ':cry:'},
        cupid:  {icon: 'emoji_cupid.png', code: ':cupid:'},
        dango:  {icon: 'emoji_dango.png', code: ':dango:'},
        dart:   {icon: 'emoji_dart.png', code: ':dart:'},
        dog:    {icon: 'emoji_dog.png', code: ':dog:'},
        dress:  {icon: 'emoji_dress.png', code: ':dress:'},
        ear:    {icon: 'emoji_ear.png', code: ':ear:'},
        email:  {icon: 'emoji_email.png', code: ':email:'},
        fish:   {icon: 'emoji_fish.png', code: ':fish:'},
        girl:   {icon: 'emoji_girl.png', code: ':girl:'},
        grin:   {icon: 'emoji_grin.png', code: ':grin:'},
        heart:  {icon: 'emoji_heart.png', code: ':heart:'},
        house:  {icon: 'emoji_house.png', code: ':house:'},
        joy:    {icon: 'emoji_joy.png', code: ':joy:'},
        kiss:   {icon: 'emoji_kiss.png', code: ':kiss:'},
        lips:   {icon: 'emoji_lips.png', code: ':lips:'},
        lock:   {icon: 'emoji_lock.png', code: ':lock:'},
        notes:  {icon: 'emoji_notes.png', code: ':notes:'},
        phone:  {icon: 'emoji_phone.png', code: ':phone:'},
        pizza:  {icon: 'emoji_pizza.png', code: ':pizza:'},
        punch:  {icon: 'emoji_punch.png', code: ':punch:'},
        ring:   {icon: 'emoji_ring.png', code: ':ring:'},
        rose:   {icon: 'emoji_rose.png', code: ':rose:'},
        shoe:   {icon: 'emoji_shoe.png', code: ':shoe:'},
        smile:  {icon: 'emoji_smile.png', code: ':smile:'},
        smirk:  {icon: 'emoji_smirk.png', code: ':smirk:'},
        sob:    {icon: 'emoji_sob.png', code: ':sob:'},
        star:   {icon: 'emoji_star.png', code: ':star:'},
        sunny:  {icon: 'emoji_sunny.png', code: ':sunny:'},
        v:      {icon: 'emoji_v.png', code: ':v:'},
        wink:   {icon: 'emoji_wink.png', code: ':wink:'},
        yum:    {icon: 'emoji_yum.png', code: ':yum:'}
    });