<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::group(['middleware' => 'language'], function () {

    /*
    |---------------------------------------------------------------------------------
    | Website (Not Authorized) (Alpha Ordered)
    |---------------------------------------------------------------------------------
    */
    Route::group(['before' => 'auth', 'middleware' => 'guest'], function () {
        // Activation
        Route::get('/activate/{token}', 'Auth\ActivationController@activate')->name('activate');

        // Application Signup
        Route::get('/application', 'Auth\ApplicationController@create')->name('application.create');
        Route::post('/application', 'Auth\ApplicationController@store')->name('application.store');

        // Authentication
        Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
        Route::post('login', 'Auth\LoginController@login')->name('');

        // Forgot Username
        Route::get('username/reminder', 'Auth\ForgotUsernameController@showForgotUsernameForm')->name('username.request');
        Route::post('username/reminder', 'Auth\ForgotUsernameController@sendUsernameReminder')->name('username.email');

        // Password Reset
        Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
        Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('');
        Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');

        // Registration
        Route::get('/register/{code?}', 'Auth\RegisterController@registrationForm')->name('registrationForm');
        Route::post('/register/{code?}', 'Auth\RegisterController@register')->name('register');
    });

    /*
    |---------------------------------------------------------------------------------
    | Website (Authorized By Key) (Alpha Ordered)
    |---------------------------------------------------------------------------------
    */
    Route::group(['before' => 'auth'], function () {
        // Announce (Pass Key Auth)
        Route::get('/announce/{passkey}', 'AnnounceController@announce')->name('announce');

        // RSS (RSS Key Auth)
        Route::get('/rss/{id}.{rsskey}', 'RssController@show')->name('rss.show.rsskey');
        Route::get('/torrent/download/{id}.{rsskey}', 'TorrentController@download')->name('torrent.download.rsskey');
    });

    /*
    |---------------------------------------------------------------------------------
    | Website (When Authorized) (Alpha Ordered)
    |---------------------------------------------------------------------------------
    */
    Route::group(['middleware' => ['auth', 'twostep', 'banned']], function () {

        // General
        Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
        Route::get('/', 'HomeController@index')->name('home.index');

        // Achievements System
        Route::group(['prefix' => 'achievements'], function () {
            Route::name('achievements.')->group(function () {
                Route::get('/', 'AchievementsController@index')->name('index');
                Route::get('/{username}', 'AchievementsController@show')->name('show');
            });
        });

        // Albums System
        Route::group(['prefix' => 'albums'], function () {
            Route::name('albums.')->group(function () {
                Route::get('/', 'AlbumController@index')->name('index');
                Route::get('/create', 'AlbumController@create')->name('create');
                Route::post('/store', 'AlbumController@store')->name('store');
                Route::get('/{id}', 'AlbumController@show')->name('show');
                Route::delete('/{id}/destroy', 'AlbumController@destroy')->name('destroy');
            });
        });

        // Articles System
        Route::group(['prefix' => 'articles'], function () {
            Route::name('articles.')->group(function () {
                Route::get('/', 'ArticleController@index')->name('index');
                Route::get('/{id}', 'ArticleController@show')->name('show');
            });
        });

        // Auth Log System
        Route::group(['prefix' => 'authentications'], function () {
            Route::name('authentications.')->group(function () {
                Route::get('/{username}', 'AuthenticationsController@show')->name('show');
            });
        });

        // RSS System
        Route::group(['prefix' => 'rss'], function () {
            Route::name('rss.')->group(function () {
                Route::get('/', 'RssController@index')->name('index');
                Route::get('/create', 'RssController@create')->name('create');
                Route::post('/store', 'RssController@store')->name('store');
                Route::get('/{id}/edit', 'RssController@edit')->name('edit');
                Route::patch('/{id}/update', 'RssController@update')->name('update');
                Route::delete('/{id}/destroy', 'RssController@destroy')->name('destroy');
            });
        });

        // TwoStep Auth System
        Route::group(['prefix' => 'twostep'], function () {
            Route::get('/needed', 'Auth\TwoStepController@showVerification')->name('verificationNeeded');
            Route::post('/verify', 'Auth\TwoStepController@verify')->name('verify');
            Route::post('/resend', 'Auth\TwoStepController@resend')->name('resend');
        });

        // Bonus System
        Route::group(['prefix' => 'bonus'], function () {
            Route::get('/', 'BonusController@bonus')->name('bonus');
            Route::get('/gifts', 'BonusController@gifts')->name('bonus_gifts');
            Route::get('/tips', 'BonusController@tips')->name('bonus_tips');
            Route::get('/store', 'BonusController@store')->name('bonus_store');
            Route::get('/gift', 'BonusController@gift')->name('bonus_gift');
            Route::post('/exchange/{id}', 'BonusController@exchange')->name('bonus_exchange');
            Route::post('/gift', 'BonusController@sendGift')->name('bonus_send_gift');
        });

        // Bookmarks System
        Route::group(['prefix' => 'bookmarks'], function () {
            Route::name('bookmarks.')->group(function () {
                Route::post('/{id}/store', 'BookmarkController@store')->name('store');
                Route::delete('/{id}/destroy', 'BookmarkController@destroy')->name('destroy');
            });
        });

        // Reports System
        Route::group(['prefix' => 'reports'], function () {
            Route::post('/torrent/{id}', 'ReportController@torrent')->name('report_torrent');
            Route::post('/request/{id}', 'ReportController@request')->name('report_request');
            Route::post('/user/{username}', 'ReportController@user')->name('report_user');
        });

        // Categories System
        Route::group(['prefix' => 'categories'], function () {
            Route::name('categories.')->group(function () {
                Route::get('/', 'CategoryController@index')->name('index');
                Route::get('/{id}', 'CategoryController@show')->name('show');
            });
        });

        // Contact Us System
        Route::group(['prefix' => 'contact'], function () {
            Route::name('contact.')->group(function () {
                Route::get('/', 'ContactController@index')->name('index');
                Route::post('/store', 'ContactController@store')->name('store');
            });
        });

        // Pages System
        Route::group(['prefix' => 'pages'], function () {
            Route::get('/', 'PageController@index')->name('pages.index');
            Route::get('/staff', 'PageController@staff')->name('staff');
            Route::get('/internal', 'PageController@internal')->name('internal');
            Route::get('/blacklist', 'PageController@blacklist')->name('blacklist');
            Route::get('/aboutus', 'PageController@about')->name('about');
            Route::get('/{id}', 'PageController@show')->where('id', '[0-9]+')->name('pages.show');
        });

        // Comments System
        Route::group(['prefix' => 'comments'], function () {
            Route::post('/article/{id}', 'CommentController@article')->name('comment_article');
            Route::post('/torrent/{id}', 'CommentController@torrent')->name('comment_torrent');
            Route::get('/thanks/{id}', 'CommentController@quickthanks')->name('comment_thanks');
            Route::post('/request/{id}', 'CommentController@request')->name('comment_request');
            Route::post('/playlist/{id}', 'CommentController@playlist')->name('comment_playlist');
            Route::post('/edit/{comment_id}', 'CommentController@editComment')->name('comment_edit');
            Route::get('/delete/{comment_id}', 'CommentController@deleteComment')->name('comment_delete');
        });

        // Extra-Stats System
        Route::group(['prefix' => 'stats'], function () {
            Route::get('/', 'StatsController@index')->name('stats');
            Route::get('/user/uploaded', 'StatsController@uploaded')->name('uploaded');
            Route::get('/user/downloaded', 'StatsController@downloaded')->name('downloaded');
            Route::get('/user/seeders', 'StatsController@seeders')->name('seeders');
            Route::get('/user/leechers', 'StatsController@leechers')->name('leechers');
            Route::get('/user/uploaders', 'StatsController@uploaders')->name('uploaders');
            Route::get('/user/bankers', 'StatsController@bankers')->name('bankers');
            Route::get('/user/seedtime', 'StatsController@seedtime')->name('seedtime');
            Route::get('/user/seedsize', 'StatsController@seedsize')->name('seedsize');
            Route::get('/torrent/seeded', 'StatsController@seeded')->name('seeded');
            Route::get('/torrent/leeched', 'StatsController@leeched')->name('leeched');
            Route::get('/torrent/completed', 'StatsController@completed')->name('completed');
            Route::get('/torrent/dying', 'StatsController@dying')->name('dying');
            Route::get('/torrent/dead', 'StatsController@dead')->name('dead');
            Route::get('/request/bountied', 'StatsController@bountied')->name('bountied');
            Route::get('/groups', 'StatsController@groups')->name('groups');
            Route::get('/groups/group/{id}', 'StatsController@group')->name('group');
            Route::get('/languages', 'StatsController@languages')->name('languages');
        });

        // Private Messages System
        Route::group(['prefix' => 'mail'], function () {
            Route::post('/searchPMInbox', 'PrivateMessageController@searchPMInbox')->name('searchPMInbox');
            Route::post('/searchPMOutbox', 'PrivateMessageController@searchPMOutbox')->name('searchPMOutbox');
            Route::get('/inbox', 'PrivateMessageController@getPrivateMessages')->name('inbox');
            Route::get('/message/{id}', 'PrivateMessageController@getPrivateMessageById')->name('message');
            Route::get('/outbox', 'PrivateMessageController@getPrivateMessagesSent')->name('outbox');
            Route::get('/create', 'PrivateMessageController@makePrivateMessage')->name('create');
            Route::get('/mark-all-read', 'PrivateMessageController@markAllAsRead')->name('mark-all-read');
            Route::get('/empty-inbox', 'PrivateMessageController@emptyInbox')->name('empty-inbox');
            Route::post('/send', 'PrivateMessageController@sendPrivateMessage')->name('send-pm');
            Route::post('/{id}/reply', 'PrivateMessageController@replyPrivateMessage')->name('reply-pm');
            Route::post('/{id}/destroy', 'PrivateMessageController@deletePrivateMessage')->name('delete-pm');
        });

        // Requests System
        Route::group(['prefix' => 'requests'], function () {
            Route::get('/filter', 'RequestController@faceted');
            Route::get('/', 'RequestController@requests')->name('requests');
            Route::get('/add/{title?}/{imdb?}/{tmdb?}', 'RequestController@addRequestForm')->name('add_request_form');
            Route::post('/add', 'RequestController@addRequest')->name('add_request');
            Route::get('/{id}/edit', 'RequestController@editRequestForm')->name('edit_request_form');
            Route::post('/{id}/edit', 'RequestController@editRequest')->name('edit_request');
            Route::get('/{id}{hash?}', 'RequestController@request')->name('request');
            Route::get('/{id}/accept', 'RequestController@approveRequest')->name('approveRequest');
            Route::post('/{id}/delete', 'RequestController@deleteRequest')->name('deleteRequest');
            Route::post('/{id}/fill', 'RequestController@fillRequest')->name('fill_request');
            Route::get('/{id}/reject', 'RequestController@rejectRequest')->name('rejectRequest');
            Route::post('/{id}/vote', 'RequestController@addBonus')->name('add_votes');
            Route::post('/{id}/claim', 'RequestController@claimRequest')->name('claimRequest');
            Route::get('/{id}/unclaim', 'RequestController@unclaimRequest')->name('unclaimRequest');
            Route::get('/{id}/reset', 'RequestController@resetRequest')->name('resetRequest')->middleware('modo');
        });

        // Torrents System
        Route::group(['prefix' => 'upload'], function () {
            Route::get('/{category_id}/{title?}/{imdb?}/{tmdb?}', 'TorrentController@uploadForm')->name('upload_form');
            Route::post('/', 'TorrentController@upload')->name('upload');
        });

        Route::group(['prefix' => 'torrents'], function () {
            Route::get('/feedizeTorrents/{type}', 'TorrentController@feedize')->name('feedizeTorrents')->middleware('modo');
            Route::get('/filter', 'TorrentController@faceted');
            Route::get('/filterSettings', 'TorrentController@filtered');
            Route::get('/', 'TorrentController@torrents')->name('torrents');
            Route::get('/{id}{hash?}', 'TorrentController@torrent')->name('torrent');
            Route::get('/{id}/peers', 'TorrentController@peers')->name('peers');
            Route::get('/{id}/history', 'TorrentController@history')->name('history');
            Route::get('/download_check/{id}', 'TorrentController@downloadCheck')->name('download_check');
            Route::get('/download/{id}', 'TorrentController@download')->name('download');
            Route::get('/view/cards', 'TorrentController@cardLayout')->name('cards');
            Route::get('/view/groupings', 'TorrentController@groupingLayout')->name('groupings');
            Route::post('/delete', 'TorrentController@deleteTorrent')->name('delete');
            Route::get('/{id}/edit', 'TorrentController@editForm')->name('edit_form');
            Route::post('/{id}/edit', 'TorrentController@edit')->name('edit');
            Route::get('/{id}/torrent_fl', 'TorrentController@grantFL')->name('torrent_fl');
            Route::get('/{id}/torrent_doubleup', 'TorrentController@grantDoubleUp')->name('torrent_doubleup');
            Route::get('/{id}/bumpTorrent', 'TorrentController@bumpTorrent')->name('bumpTorrent');
            Route::get('/{id}/torrent_sticky', 'TorrentController@sticky')->name('torrent_sticky');
            Route::get('/{id}/torrent_feature', 'TorrentController@grantFeatured')->name('torrent_feature');
            Route::get('/{id}/reseed', 'TorrentController@reseedTorrent')->name('reseed');
            Route::post('/{id}/tip_uploader', 'BonusController@tipUploader')->name('tip_uploader');
            Route::get('/{id}/freeleech_token', 'TorrentController@freeleechToken')->name('freeleech_token');
            Route::get('/similar/{category_id}.{tmdb}', 'TorrentController@similar')->name('torrents.similar');
        });

        // Warnings System
        Route::group(['prefix' => 'warnings'], function () {
            Route::get('/{id}/deactivate', 'WarningController@deactivate')->name('deactivateWarning');
            Route::get('/{username}/mass-deactivate', 'WarningController@deactivateAllWarnings')->name('massDeactivateWarnings');
            Route::delete('/{id}', 'WarningController@deleteWarning')->name('deleteWarning');
            Route::delete('/{username}/mass-delete', 'WarningController@deleteAllWarnings')->name('massDeleteWarnings');
            Route::get('/{id}/restore', 'WarningController@restoreWarning')->name('restoreWarning');
            Route::get('/{username}', 'WarningController@show')->name('warnings.show');
        });

        // Users System
        Route::group(['prefix' => 'users'], function () {
            Route::get('/{username}', 'UserController@show')->name('users.show');
            Route::get('/{username}/edit', 'UserController@editProfileForm')->name('user_edit_profile_form');
            Route::post('/{username}/edit', 'UserController@editProfile')->name('user_edit_profile');
            Route::post('/{username}/photo', 'UserController@changePhoto')->name('user_change_photo');
            Route::get('/{username}/activate/{token}', 'UserController@activate')->name('user_activate');
            Route::post('/{username}/about', 'UserController@changeAbout')->name('user_change_about');
            Route::post('/{username}/photo', 'UserController@changeTitle')->name('user_change_title');
            Route::get('/{username}/banlog', 'UserController@getBans')->name('banlog');
            Route::post('/{username}/userFilters', 'UserController@myFilter')->name('myfilter');
            Route::get('/{username}/downloadHistoryTorrents', 'UserController@downloadHistoryTorrents')->name('download_history_torrents');
            Route::get('/{username}/seeds', 'UserController@seeds')->name('user_seeds');
            Route::get('/{username}/resurrections', 'UserController@resurrections')->name('user_resurrections');
            Route::get('/{username}/requested', 'UserController@requested')->name('user_requested');
            Route::get('/{username}/active', 'UserController@active')->name('user_active');
            Route::get('/{username}/torrents', 'UserController@torrents')->name('user_torrents');
            Route::get('/{username}/uploads', 'UserController@uploads')->name('user_uploads');
            Route::get('/{username}/downloads', 'UserController@downloads')->name('user_downloads');
            Route::get('/{username}/unsatisfieds', 'UserController@unsatisfieds')->name('user_unsatisfieds');
            Route::get('/{username}/topics', 'UserController@topics')->name('user_topics');
            Route::get('/{username}/posts', 'UserController@posts')->name('user_posts');
            Route::get('/{username}/followers', 'UserController@followers')->name('user_followers');
            Route::get('/{username}/bookmarks', 'BookmarkController@index')->name('bookmarks.index');

            Route::get('/{username}/settings', 'UserController@settings')->name('user_settings');
            Route::get('/{username}/settings/privacy{hash?}', 'UserController@privacy')->name('user_privacy');
            Route::get('/{username}/settings/security{hash?}', 'UserController@security')->name('user_security');
            Route::get('/{username}/settings/notification{hash?}', 'UserController@notification')->name('user_notification');
            Route::post('/{username}/settings/change_settings', 'UserController@changeSettings')->name('change_settings');
            Route::post('/{username}/settings/change_password', 'UserController@changePassword')->name('change_password');
            Route::post('/{username}/settings/change_email', 'UserController@changeEmail')->name('change_email');
            Route::post('/{username}/settings/change_pid', 'UserController@changePID')->name('change_pid');
            Route::post('/{username}/settings/change_rid', 'UserController@changeRID')->name('change_rid');
            Route::post('/{username}/settings/change_api_token', 'UserController@changeApiToken')->name('change_api_token');
            Route::get('/{username}/settings/notification/disable', 'UserController@disableNotifications')->name('notification_disable');
            Route::get('/{username}/settings/notification/enable', 'UserController@enableNotifications')->name('notification_enable');
            Route::post('/{username}/settings/notification/account', 'UserController@changeAccountNotification')->name('notification_account');
            Route::post('/{username}/settings/notification/following', 'UserController@changeFollowingNotification')->name('notification_following');
            Route::post('/{username}/settings/notification/forum', 'UserController@changeForumNotification')->name('notification_forum');
            Route::post('/{username}/settings/notification/subscription', 'UserController@changeSubscriptionNotification')->name('notification_subscription');
            Route::post('/{username}/settings/notification/mention', 'UserController@changeMentionNotification')->name('notification_mention');
            Route::post('/{username}/settings/notification/torrent', 'UserController@changeTorrentNotification')->name('notification_torrent');
            Route::post('/{username}/settings/notification/bon', 'UserController@changeBonNotification')->name('notification_bon');
            Route::post('/{username}/settings/notification/request', 'UserController@changeRequestNotification')->name('notification_request');
            Route::post('/{username}/settings/privacy/profile', 'UserController@changeProfile')->name('privacy_profile');
            Route::post('/{username}/settings/privacy/forum', 'UserController@changeForum')->name('privacy_forum');
            Route::post('/{username}/settings/privacy/torrent', 'UserController@changeTorrent')->name('privacy_torrent');
            Route::post('/{username}/settings/privacy/follower', 'UserController@changeFollower')->name('privacy_follower');
            Route::post('/{username}/settings/privacy/achievement', 'UserController@changeAchievement')->name('privacy_achievement');
            Route::post('/{username}/settings/privacy/request', 'UserController@changeRequest')->name('privacy_request');
            Route::post('/{username}/settings/privacy/other', 'UserController@changeOther')->name('privacy_other');
            Route::post('/{username}/settings/change_twostep', 'UserController@changeTwoStep')->name('change_twostep');
            Route::get('/{username}/settings/hidden', 'UserController@makeHidden')->name('user_hidden');
            Route::get('/{username}/settings/visible', 'UserController@makeVisible')->name('user_visible');
            Route::get('/{username}/settings/private', 'UserController@makePrivate')->name('user_private');
            Route::get('/{username}/settings/public', 'UserController@makePublic')->name('user_public');
            Route::post('/accept-rules', 'UserController@acceptRules')->name('accept.rules');
            Route::get('/{username}/seedboxes', 'SeedboxController@index')->name('seedboxes.index');
            Route::post('/{username}/seedboxes', 'SeedboxController@store')->name('seedboxes.store');
            Route::delete('/seedboxes/{id}', 'SeedboxController@destroy')->name('seedboxes.destroy');
        });

        // Wishlist System
        Route::group(['prefix' => 'wishes'], function () {
            Route::name('wishes.')->group(function () {
                Route::get('/{username}', 'WishController@index')->name('index');
                Route::post('/store', 'WishController@store')->name('store');
                Route::get('/{id}/destroy', 'WishController@destroy')->name('destroy');
            });
        });

        // Follow System
        Route::group(['prefix' => 'follow'], function () {
            Route::name('follow.')->group(function () {
                Route::post('/{username}', 'FollowController@store')->name('store');
                Route::delete('/{username}', 'FollowController@destroy')->name('destroy');
            });
        });

        // Thank System
        Route::get('/thanks/{id}', 'ThankController@store')->name('thanks.store');

        // Language System
        Route::get('/{locale}/back', 'LanguageController@back')->name('back');

        // Invite System
        Route::group(['prefix' => 'invites'], function () {
            Route::name('invites.')->group(function () {
                Route::get('/create', 'InviteController@create')->name('create');
                Route::post('/store', 'InviteController@store')->name('store');
                Route::post('/{id}/send', 'InviteController@send')->where('id', '[0-9]+')->name('send');
                Route::get('/{username}', 'InviteController@index')->name('index');
            });
        });

        // Poll System
        Route::group(['prefix' => 'polls'], function () {
            Route::get('/', 'PollController@index')->name('polls');
            Route::post('/vote', 'PollController@vote');
            Route::get('/{id}', 'PollController@show')->where('id', '[0-9]+')->name('poll');
            Route::get('/{id}/result', 'PollController@result')->name('poll_results');
        });

        // Graveyard System
        Route::group(['prefix' => 'graveyard'], function () {
            Route::name('graveyard.')->group(function () {
                Route::get('/filter', 'GraveyardController@faceted');
                Route::get('/', 'GraveyardController@index')->name('index');
                Route::post('/{id}/store', 'GraveyardController@store')->name('store');
                Route::delete('/{id}/destroy', 'GraveyardController@destroy')->name('destroy');
            });
        });

        // Notifications System
        Route::group(['prefix' => 'notifications'], function () {
            Route::name('notifications.')->group(function () {
                Route::get('/filter', 'NotificationController@faceted');
                Route::get('/', 'NotificationController@index')->name('index');
                Route::post('/{id}/update', 'NotificationController@update')->name('update');
                Route::post('/updateall', 'NotificationController@updateAll')->name('updateall');
                Route::delete('/{id}/destroy', 'NotificationController@destroy')->name('destroy');
                Route::delete('/destroyall', 'NotificationController@destroyAll')->name('destroyall');
                Route::get('/{id}', 'NotificationController@show')->name('show');
            });
        });

        // Images System
        Route::group(['prefix' => 'images'], function () {
            Route::name('images.')->group(function () {
                Route::get('/{id}/create', 'ImageController@create')->name('create');
                Route::post('/store', 'ImageController@store')->name('store');
                Route::get('/{id}/download', 'ImageController@download')->name('download');
                Route::delete('/{id}/destroy', 'ImageController@destroy')->name('destroy');
            });
        });

        // Playlist System
        Route::group(['prefix' => 'playlists'], function () {
            Route::name('playlists.')->group(function () {
                Route::get('/', 'PlaylistController@index')->name('index');
                Route::get('/create', 'PlaylistController@create')->name('create');
                Route::post('/store', 'PlaylistController@store')->name('store');
                Route::get('/{id}', 'PlaylistController@show')->where('id', '[0-9]+')->name('show');
                Route::get('/{id}/edit', 'PlaylistController@edit')->name('edit');
                Route::patch('/{id}/update', 'PlaylistController@update')->name('update');
                Route::delete('/{id}/destroy', 'PlaylistController@destroy')->name('destroy');
                Route::post('/attach', 'PlaylistTorrentController@store')->name('attach');
                Route::delete('/{id}/detach', 'PlaylistTorrentController@destroy')->name('detach');
            });
        });

        // Subtitles System
        Route::group(['prefix' => 'subtitles'], function () {
            Route::name('subtitles.')->group(function () {
                Route::get('/', 'SubtitleController@index')->name('index');
                Route::get('/create/{torrent_id}', 'SubtitleController@create')->where('id', '[0-9]+')->name('create');
                Route::post('/store', 'SubtitleController@store')->name('store');
                Route::post('/{id}/update', 'SubtitleController@update')->name('update');
                Route::delete('/{id}/delete', 'SubtitleController@destroy')->name('destroy');
                Route::get('/{id}/download', 'SubtitleController@download')->name('download');
                Route::get('/filter', 'SubtitleController@faceted');
            });
        });
    });

    /*
    |---------------------------------------------------------------------------------
    | ChatBox Routes Group (When Authorized) (Alpha Ordered)
    |---------------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'chatbox', 'middleware' => ['auth', 'twostep', 'banned'], 'namespace' => 'API'], function () {
        Route::get('/', 'ChatController@index');
        Route::get('/chatrooms', 'ChatController@fetchChatrooms');
        Route::post('/change-chatroom', 'ChatController@changeChatroom');
        Route::get('/messages', 'ChatController@fetchMessages');
        Route::post('/messages', 'ChatController@sendMessage');
    });

    /*
    |---------------------------------------------------------------------------------
    | Forums Routes Group (When Authorized) (Alpha Ordered)
    |---------------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'forums', 'middleware' => ['auth', 'twostep', 'banned']], function () {
        // Forum System
        Route::name('forums.')->group(function () {
            Route::get('/', 'ForumController@index')->name('index');
            Route::get('/{id}', 'ForumController@show')->where('id', '[0-9]+')->name('show');
        });

        // Forum Category System
        Route::group(['prefix' => 'categories'], function () {
            Route::name('forums.categories.')->group(function () {
                Route::get('/{id}', 'ForumCategoryController@show')->where('id', '[0-9]+')->name('show');
            });
        });

        // Posts System
        Route::group(['prefix' => 'posts'], function () {
            Route::post('/topic/{id}/reply', 'PostController@reply')->name('forum_reply');
            Route::get('/posts/{id}/post-{postId}/edit', 'PostController@postEditForm')->name('forum_post_edit_form');
            Route::post('/posts/{postId}/edit', 'PostController@postEdit')->name('forum_post_edit');
            Route::get('/posts/{postId}/delete', 'PostController@postDelete')->name('forum_post_delete');
        });

        // Search Forums
        Route::get('/subscriptions', 'ForumController@subscriptions')->name('forum_subscriptions');
        Route::get('/latest/topics', 'ForumController@latestTopics')->name('forum_latest_topics');
        Route::get('/latest/posts', 'ForumController@latestPosts')->name('forum_latest_posts');
        Route::get('/search', 'ForumController@search')->name('forum_search_form');

        Route::group(['prefix' => 'topics'], function () {
            // Create New Topic
            Route::get('/forum/{id}/new-topic', 'TopicController@addForm')->name('forum_new_topic_form');
            Route::post('/forum/{id}/new-topic', 'TopicController@newTopic')->name('forum_new_topic');
            // View Topic
            Route::get('/{id}{page?}{post?}', 'TopicController@topic')->name('forum_topic');
            // Close Topic
            Route::get('/{id}/close', 'TopicController@closeTopic')->name('forum_close');
            // Open Topic
            Route::get('/{id}/open', 'TopicController@openTopic')->name('forum_open');
            //
            Route::post('/posts/tip_poster', 'BonusController@tipPoster')->name('tip_poster');

            // Edit Topic
            Route::get('/{id}/edit', 'TopicController@editForm')->name('forum_edit_topic_form');
            Route::post('/{id}/edit', 'TopicController@editTopic')->name('forum_edit_topic');
            // Delete Topic
            Route::get('/{id}/delete', 'TopicController@deleteTopic')->name('forum_delete_topic');
            // Pin Topic
            Route::get('/{id}/pin', 'TopicController@pinTopic')->name('forum_pin_topic');
            // Unpin Topic
            Route::get('/{id}/unpin', 'TopicController@unpinTopic')->name('forum_unpin_topic');
        });

        // Topic Label System
        Route::group(['prefix' => 'topics', 'middleware' => 'modo'], function () {
            Route::name('topics.')->group(function () {
                Route::get('/{id}/approve', 'TopicLabelController@approve')->name('approve');
                Route::get('/{id}/deny', 'TopicLabelController@deny')->name('deny');
                Route::get('/{id}/solve', 'TopicLabelController@solve')->name('solve');
                Route::get('/{id}/invalid', 'TopicLabelController@invalid')->name('invalid');
                Route::get('/{id}/bug', 'TopicLabelController@bug')->name('bug');
                Route::get('/{id}/suggest', 'TopicLabelController@suggest')->name('suggest');
                Route::get('/{id}/implement', 'TopicLabelController@implement')->name('implement');
            });
        });

        // Like - Dislike System
        Route::any('/like/post/{postId}', 'LikeController@store')->name('like');
        Route::any('/dislike/post/{postId}', 'LikeController@destroy')->name('dislike');

        // Subscription System
        Route::get('/subscribe/topic/{route}.{topic}', 'SubscriptionController@subscribeTopic')->name('subscribe_topic');
        Route::get('/unsubscribe/topic/{route}.{topic}', 'SubscriptionController@unsubscribeTopic')->name('unsubscribe_topic');
        Route::get('/subscribe/forum/{route}.{forum}', 'SubscriptionController@subscribeForum')->name('subscribe_forum');
        Route::get('/unsubscribe/forum/{route}.{forum}', 'SubscriptionController@unsubscribeForum')->name('unsubscribe_forum');
    });

    /*
    |---------------------------------------------------------------------------------
    | Staff Dashboard Routes Group (When Authorized And A Staff Group) (Alpha Ordered)
    |---------------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'twostep', 'modo', 'banned'], 'namespace' => 'Staff'], function () {

        // Staff Dashboard
        Route::name('staff.dashboard.')->group(function () {
            Route::get('/', 'HomeController@index')->name('index');
        });

        // Articles System
        Route::group(['prefix' => 'articles'], function () {
            Route::name('staff.articles.')->group(function () {
                Route::get('/', 'ArticleController@index')->name('index');
                Route::get('/create', 'ArticleController@create')->name('create');
                Route::post('/store', 'ArticleController@store')->name('store');
                Route::get('/{id}/edit', 'ArticleController@edit')->name('edit');
                Route::post('/{id}/update', 'ArticleController@update')->name('update');
                Route::delete('/{id}/destroy', 'ArticleController@destroy')->name('destroy');
            });
        });

        // Applications System
        Route::group(['prefix' => 'applications'], function () {
            Route::name('staff.applications.')->group(function () {
                Route::get('/', 'ApplicationController@index')->name('index');
                Route::get('/{id}', 'ApplicationController@show')->where('id', '[0-9]+')->name('show');
                Route::post('/{id}/approve', 'ApplicationController@approve')->name('approve');
                Route::post('/{id}/reject', 'ApplicationController@reject')->name('reject');
            });
        });

        // Audit Log
        Route::group(['prefix' => 'audits'], function () {
            Route::name('staff.audits.')->group(function () {
                Route::get('/', 'AuditController@index')->name('index');
                Route::delete('/{id}/destroy', 'AuditController@destroy')->name('destroy');
            });
        });

        // Authentications Log
        Route::group(['prefix' => 'authentications'], function () {
            Route::name('staff.authentications.')->group(function () {
                Route::get('/', 'AuthenticationController@index')->name('index');
            });
        });

        // Backup System
        Route::group(['prefix' => 'backups'], function () {
            Route::name('staff.backups.')->group(function () {
                Route::get('/', 'BackupController@index')->name('index');
                Route::post('/full', 'BackupController@create')->name('full');
                Route::post('/files', 'BackupController@files')->name('files');
                Route::post('/database', 'BackupController@database')->name('database');
                Route::get('/download/{file_name?}', 'BackupController@download')->name('download');
                Route::delete('/destroy/{file_name?}', 'BackupController@destroy')->where('file_name', '(.*)')->name('destroy');
            });
        });

        // Ban System
        Route::group(['prefix' => 'bans'], function () {
            Route::name('staff.bans.')->group(function () {
                Route::get('/', 'BanController@index')->name('index');
                Route::post('/{username}/store', 'BanController@store')->name('store');
                Route::post('/{username}/update', 'BanController@update')->name('update');
            });
        });

        // Categories System
        Route::group(['prefix' => 'categories'], function () {
            Route::name('staff.categories.')->group(function () {
                Route::get('/', 'CategoryController@index')->name('index');
                Route::get('/create', 'CategoryController@create')->name('create');
                Route::post('/store', 'CategoryController@store')->name('store');
                Route::get('/{id}/edit', 'CategoryController@edit')->name('edit');
                Route::patch('/{id}/update', 'CategoryController@update')->name('update');
                Route::delete('/{id}/destroy', 'CategoryController@destroy')->name('destroy');
            });
        });

        // Chat Bots System
        Route::group(['prefix' => 'chat'], function () {
            Route::name('staff.bots.')->group(function () {
                Route::get('/bots', 'ChatBotController@index')->name('index');
                Route::get('/bots/{id}/edit', 'ChatBotController@edit')->name('edit');
                Route::patch('/bots/{id}/update', 'ChatBotController@update')->name('update');
                Route::delete('/bots/{id}/destroy', 'ChatBotController@destroy')->name('destroy');
                Route::get('/bots/{id}/disable', 'ChatBotController@disable')->name('disable');
                Route::get('/bots/{id}/enable', 'ChatBotController@enable')->name('enable');
            });
        });

        // Chat Rooms System
        Route::group(['prefix' => 'chat'], function () {
            Route::name('staff.rooms.')->group(function () {
                Route::get('/rooms', 'ChatRoomController@index')->name('index');
                Route::post('/rooms/store', 'ChatRoomController@store')->name('store');
                Route::post('/rooms/{id}/update', 'ChatRoomController@update')->name('update');
                Route::delete('/rooms/{id}/destroy', 'ChatRoomController@destroy')->name('destroy');
            });
        });

        // Chat Statuses System
        Route::group(['prefix' => 'chat'], function () {
            Route::name('staff.statuses.')->group(function () {
                Route::get('/statuses', 'ChatStatusController@index')->name('index');
                Route::post('/statuses/store', 'ChatStatusController@store')->name('store');
                Route::post('/statuses/{id}/update', 'ChatStatusController@update')->name('update');
                Route::delete('/statuses/{id}/destroy', 'ChatStatusController@destroy')->name('destroy');
            });
        });

        // Cheaters
        Route::group(['prefix' => 'cheaters'], function () {
            Route::name('staff.cheaters.')->group(function () {
                Route::get('/ghost-leechers', 'CheaterController@index')->name('index');
            });
        });

        // Codebase Version Check
        Route::group(['prefix' => 'UNIT3D'], function () {
            Route::get('/', 'VersionController@checkVersion');
        });

        // Commands
        Route::group(['prefix' => 'commands'], function () {
            Route::get('/', 'CommandController@index')->name('staff.commands.index');
            Route::get('/maintance-enable', 'CommandController@maintanceEnable');
            Route::get('/maintance-disable', 'CommandController@maintanceDisable');
            Route::get('/clear-cache', 'CommandController@clearCache');
            Route::get('/clear-view-cache', 'CommandController@clearView');
            Route::get('/clear-route-cache', 'CommandController@clearRoute');
            Route::get('/clear-config-cache', 'CommandController@clearConfig');
            Route::get('/clear-all-cache', 'CommandController@clearAllCache');
            Route::get('/set-all-cache', 'CommandController@setAllCache');
            Route::get('/clear-compiled', 'CommandController@clearCompiled');
            Route::get('/test-email', 'CommandController@testEmail');
        });

        // Flush System
        Route::group(['prefix' => 'flush'], function () {
            Route::name('staff.flush.')->group(function () {
                Route::get('/peers', 'FlushController@peers')->name('peers');
                Route::get('/chat', 'FlushController@chat')->name('chat');
            });
        });

        // Forums System
        Route::group(['prefix' => 'forums'], function () {
            Route::name('staff.forums.')->group(function () {
                Route::get('/', 'ForumController@index')->name('index');
                Route::get('/create', 'ForumController@create')->name('create');
                Route::post('/store', 'ForumController@store')->name('store');
                Route::get('/{id}/edit', 'ForumController@edit')->name('edit');
                Route::post('/{id}/update', 'ForumController@update')->name('update');
                Route::delete('/{id}/destroy', 'ForumController@destroy')->name('destroy');
            });
        });

        // Groups System
        Route::group(['prefix' => 'groups'], function () {
            Route::name('staff.groups.')->group(function () {
                Route::get('/', 'GroupController@index')->name('index');
                Route::get('/create', 'GroupController@create')->name('create');
                Route::post('/store', 'GroupController@store')->name('store');
                Route::get('/{id}/edit', 'GroupController@edit')->name('edit');
                Route::post('/{id}/update', 'GroupController@update')->name('update');
            });
        });

        // Invites Log
        Route::group(['prefix' => 'invites'], function () {
            Route::name('staff.invites.')->group(function () {
                Route::get('/', 'InviteController@index')->name('index');
            });
        });

        // Mass Actions
        Route::group(['prefix' => 'mass-actions'], function () {
            Route::get('/validate-users', 'MassActionController@update')->name('staff.mass-actions.validate');
            Route::get('/mass-pm', 'MassActionController@create')->name('staff.mass-pm.create');
            Route::post('/mass-pm/store', 'MassActionController@store')->name('staff.mass-pm.store');
        });

        // Media Lanuages (Languages Used To Populate Language Dropdowns For Subtitles / Audios / Etc.)
        Route::group(['prefix' => 'media-languages'], function () {
            Route::name('staff.media_languages.')->group(function () {
                Route::get('/', 'MediaLanguageController@index')->name('index');
                Route::get('/create', 'MediaLanguageController@create')->name('create');
                Route::post('/store', 'MediaLanguageController@store')->name('store');
                Route::get('/{id}/edit', 'MediaLanguageController@edit')->name('edit');
                Route::post('/{id}/update', 'MediaLanguageController@update')->name('update');
                Route::delete('/{id}/delete', 'MediaLanguageController@destroy')->name('destroy');
            });
        });

        // Moderation System
        Route::group(['prefix' => 'moderation'], function () {
            Route::name('staff.moderation.')->group(function () {
                Route::get('/', 'ModerationController@index')->name('index');
                Route::get('/{id}/approve', 'ModerationController@approve')->name('approve');
                Route::post('/reject', 'ModerationController@reject')->name('reject');
                Route::post('/postpone', 'ModerationController@postpone')->name('postpone');
            });
        });

        //Pages System
        Route::group(['prefix' => 'pages'], function () {
            Route::name('staff.pages.')->group(function () {
                Route::get('/', 'PageController@index')->name('index');
                Route::get('/create', 'PageController@create')->name('create');
                Route::post('/store', 'PageController@store')->name('store');
                Route::get('/{id}/edit', 'PageController@edit')->name('edit');
                Route::post('/{id}/update', 'PageController@update')->name('update');
                Route::delete('/{id}/destroy', 'PageController@destroy')->name('destroy');
            });
        });

        // Polls System
        Route::group(['prefix' => 'polls'], function () {
            Route::name('staff.polls.')->group(function () {
                Route::get('/', 'PollController@index')->name('index');
                Route::get('/{id}', 'PollController@show')->where('id', '[0-9]+')->name('show');
                Route::get('/create', 'PollController@create')->name('create');
                Route::post('/store', 'PollController@store')->name('store');
                Route::get('/{id}/edit', 'PollController@edit')->name('edit');
                Route::post('/{id}/update', 'PollController@update')->name('update');
                Route::delete('/{id}/destroy', 'PollController@destroy')->name('destroy');
            });
        });

        // Registered Seedboxes
        Route::group(['prefix' => 'seedboxes'], function () {
            Route::name('staff.seedboxes.')->group(function () {
                Route::get('/', 'SeedboxController@index')->name('index');
                Route::delete('/{id}/destroy', 'SeedboxController@destroy')->name('destroy');
            });
        });

        // Reports
        Route::group(['prefix' => 'reports'], function () {
            Route::name('staff.reports.')->group(function () {
                Route::get('/', 'ReportController@index')->name('index');
                Route::get('/{id}', 'ReportController@show')->where('id', '[0-9]+')->name('show');
                Route::post('/{id}/solve', 'ReportController@update')->name('update');
            });
        });

        // Resolutions
        Route::group(['prefix' => 'resolutions'], function () {
            Route::name('staff.resolutions.')->group(function () {
                Route::get('/', 'ResolutionController@index')->name('index');
                Route::get('/create', 'ResolutionController@create')->name('create');
                Route::post('/store', 'ResolutionController@store')->name('store');
                Route::get('/{id}/edit', 'ResolutionController@edit')->name('edit');
                Route::patch('/{id}/update', 'ResolutionController@update')->name('update');
                Route::delete('/{id}/destroy', 'ResolutionController@destroy')->name('destroy');
            });
        });

        // RSS System
        Route::group(['prefix' => 'rss'], function () {
            Route::name('staff.rss.')->group(function () {
                Route::get('/', 'RssController@index')->name('index');
                Route::get('/create', 'RssController@create')->name('create');
                Route::post('/store', 'RssController@store')->name('store');
                Route::get('/{id}/edit', 'RssController@edit')->name('edit');
                Route::patch('/{id}/update', 'RssController@update')->name('update');
                Route::delete('/{id}/destroy', 'RssController@destroy')->name('destroy');
            });
        });

        // Tag (Genres)
        Route::group(['prefix' => 'tags'], function () {
            Route::name('staff.tags.')->group(function () {
                Route::get('/', 'TagController@index')->name('index');
                Route::get('/create', 'TagController@create')->name('create');
                Route::post('/store', 'TagController@store')->name('store');
                Route::get('/{id}/edit', 'TagController@edit')->name('edit');
                Route::post('/{id}/update', 'TagController@update')->name('update');
            });
        });

        // Types
        Route::group(['prefix' => 'types'], function () {
            Route::name('staff.types.')->group(function () {
                Route::get('/', 'TypeController@index')->name('index');
                Route::get('/create', 'TypeController@create')->name('create');
                Route::post('/store', 'TypeController@store')->name('store');
                Route::get('/{id}/edit', 'TypeController@edit')->name('edit');
                Route::patch('/{id}/update', 'TypeController@update')->name('update');
                Route::delete('/{id}/destroy', 'TypeController@destroy')->name('destroy');
            });
        });

        // User Gifting (From System)
        Route::group(['prefix' => 'gifts'], function () {
            Route::name('staff.gifts.')->group(function () {
                Route::get('/', 'GiftController@index')->name('index');
                Route::post('/store', 'GiftController@store')->name('store');
            });
        });

        // User Staff Notes
        Route::group(['prefix' => 'notes'], function () {
            Route::name('staff.notes.')->group(function () {
                Route::get('/', 'NoteController@index')->name('index');
                Route::post('/{username}/store', 'NoteController@store')->name('store');
                Route::delete('/{id}/destroy', 'NoteController@destroy')->name('destroy');
            });
        });

        // User Tools TODO: Leaving since we will be refactoring users and roles
        Route::group(['prefix' => 'users'], function () {
            Route::get('/', 'UserController@index')->name('user_search');
            Route::get('/search', 'UserController@search')->name('user_results');
            Route::post('/{username}/edit', 'UserController@edit')->name('user_edit');
            Route::get('/{username}/settings', 'UserController@settings')->name('user_setting');
            Route::post('/{username}/permissions', 'UserController@permissions')->name('user_permissions');
            Route::post('/{username}/password', 'UserController@password')->name('user_password');
            Route::get('/{username}/destroy', 'UserController@destroy')->name('user_delete');
        });

        // Warnings Log
        Route::group(['prefix' => 'warnings'], function () {
            Route::name('staff.warnings.')->group(function () {
                Route::get('/', 'WarningController@index')->name('index');
            });
        });
    });
});
