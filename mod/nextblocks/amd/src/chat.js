/**
 *
 * @module      mod_nextblocks/chat
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax'], function($, ajax) {
    const ChatManager = {
        lastTimestamp: 0,
        cmid: 0,
        instanceid: 0,
        fullname: '',

        init: function(cmid, instanceid, username) {
            this.cmid = cmid;
            this.instanceid = instanceid;
            this.fullname = username;
            this.setupEventListeners();
            this.loadInitialMessages();
            this.startPolling();
        },

        setupEventListeners: function() {
            $('.msg-form').submit((e) => {
                e.preventDefault();
                const message = $('#msg').val().trim();
                if (message) {
                    this.sendMessage(message);
                    $('#msg').val('');
                }
                return false;
            });
        },

        loadInitialMessages: function() {
            ajax.call([{
                methodname: 'mod_nextblocks_get_messages',
                args: {
                    messagecount: 50,
                    nextblocksid: this.instanceid,
                    cmid: this.cmid
                },
                done: (messages) => {
                    this.processMessages(messages);
                    $('#messages').scrollTop($('#messages')[0].scrollHeight);
                }
            }]);
        },

        sendMessage: function(message) {
            const timestamp = Math.floor(Date.now() / 1000);
            ajax.call([{
                methodname: 'mod_nextblocks_save_message',
                args: {
                    message: message,
                    username: this.fullname,
                    nextblocksid: this.instanceid,
                    cmid: this.cmid,
                    timestamp: timestamp
                },
                done: () => {
                    this.displayMessage({
                        message: message,
                        username: this.fullname,
                        timestamp: timestamp
                    });

                    // Scroll to bottom.
                    $('#messages').scrollTop($('#messages')[0].scrollHeight);
                }
            }]);
        },

        startPolling: function() {
            setInterval(() => {
                ajax.call([{
                    methodname: 'mod_nextblocks_get_messages',
                    args: {
                        messagecount: 50,
                        nextblocksid: this.instanceid,
                        cmid: this.cmid
                    },
                    done: this.processMessages.bind(this)
                }]);
            }, 3000);
        },

        processMessages: function(messages) {
            messages.forEach(msg => {
                if (msg.timestamp > this.lastTimestamp) {
                    this.displayMessage(msg);
                }
            });

            if (messages.length > 0) {
                $('#messages').scrollTop($('#messages')[0].scrollHeight);
            }
        },

        displayMessage: function(msg) {
            this.lastTimestamp = Math.max(this.lastTimestamp, msg.timestamp);
            const date = new Date(msg.timestamp * 1000);
            const timeString = date.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const dateString = `${day}/${month}/${year}`;

            $('#messages').append(`
                <div class="message">
                    <div class="message-header">
                        <span class="user">${msg.username}</span>
                        <span class="time">${timeString} ${dateString}</span>
                    </div>
                    <div class="message-text">${msg.message}</div>
                </div>
            `);
        }
    };

    return {
        init: ChatManager.init.bind(ChatManager)
    };
});