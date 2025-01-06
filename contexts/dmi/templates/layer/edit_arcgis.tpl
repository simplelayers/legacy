<!--{$subnav}-->
<style>
    iframe#selectorFrame {
        display: flex;
        align-content: stretch;
        align-items: stretch;
        flex-direction: row;
        flex-wrap: nowrap;
        width: 100%;
        justify-content: center;
        height: calc(100vh - 14rem);
    }
</style>
<script>
    $(() => {
        $('.contentarea').toggleClass('padless',true);
        $('.contentarea').toggleClass('flex-content');
    });
</script>
<script>
    const selectorFrame = $('#selectorFrame')[0];

    const appURL = '<!--{$appURL}-->';
    const _storedMessages = [];
    let mapRegistered = false;
    let _frameRef = null;

    $(() => {
        mapFrameId = -1;
        SetupMessageListener($('#selectorFrame')[0]);

        $('#selectorFrame')[0].src = "<!--{$appsPath}-->/dmi-pages/data/arcgis/config/layer:<!--{$layerId}-->?key=<!--{$pageKey}-->";



    });

    function SetupMessageListener(frameRef) {
        _frameRef = frameRef;
        window.addEventListener("message", (event) => {
            if (!frameRef) return;
            // eslint-disable-next-line powerbi-visuals/no-inner-outer-html
            // Check the origin of the data! 
            let messageData = event.data;
            if ((typeof event.data === 'string')) {
                messageData = JSON.parse(event.data);
               
            }
            const message = messageData;
            if (message?.source?.indexOf('react-') > -1) {
                return;
            }
            if (!message) return;
            console.log(message);
            if (message['type'] == 'sl-app-message') {
                const msg = message;
                let messageName = msg.name;
                const messageNameParts = msg.name.split('-');
                messageName = '';
                for (const part of messageNameParts) {
                    messageName += part.slice(0, 1).toUpperCase() + part.slice(1);
                }
                console.log(`Handling ${messageName}`);
                if (window['Handle' + messageName]) {
                    window['Handle' + messageName](message);
                    return;
                }

            }
        }, false);

    }

    function HandleRegisterApp(message) {
        console.log('in handle register app', JSON.stringify(message));
        if (message.name != 'register-app') return;
        mapFrameId = message.src;
        mapRegistered = true;
        _storedMessages.unshift({
            messageName: 'app-registered',
            payload: { context: 'sl-dmi-import_arcgis' }
        })
        SendMessage()
        if (this.lastUpdate !== undefined) {
            this.update(this.lastUpdate);
        }
    }
    function HandleAppReady(message) {
        console.log(message);
    }

    function HandleRedirect(message) {
        window.location.href = "<!--{$pageArgsInfo['layer_info']}-->";
    }

    function SendMessage() {
        if (mapRegistered) {
            const msgInfo = _storedMessages.shift();
            /*if (!this.id || !this.endPoint) {
                _storedMessages.unshift(msgInfo);
                return;
            }*/
            const message = MakeMessage(msgInfo.messageName, msgInfo.payload);
            console.log('sending message', message);
            if(_frameRef) {
                _frameRef.contentWindow.postMessage(JSON.stringify(message), '*');
            }   
            window.requestAnimationFrame(() => {
                if (_storedMessages.length > 0) {
                    this.SendMessage();
                }
            })
        }
    }
    messages = [];

    function MakeMessage(name, content) {
        // if (!this.id || !this.endPoint) return;
        const message = {
            name: (name),
            content: content,
            src: mapFrameId,
            url: window.location.href,
            type: 'sl-app-message'
        }
        return message
    }
</script>
<iframe id="selectorFrame"></iframe>