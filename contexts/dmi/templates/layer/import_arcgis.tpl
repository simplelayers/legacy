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
        height: calc(100vh - 15.2em);
    }
</style>
<script>
    $(() => {
        const selectorFrame = $('#selectorFrame')[0];
        _storedMessages = [];
        mapFrameId = -1;
        mapRegistered = false;
        $('#selectorFrame')[0].src = "<!--{$appsPath}-->/dmi-pages/data/arcgis/config";
        $('.contentarea').toggleClass('padless');
        $('.contentarea').toggleClass('flex-content');

        function SetupMessageListener(frameRef) {
            window.addEventListener("message", (event) => {
                if (!frameRef) return;
                // eslint-disable-next-line powerbi-visuals/no-inner-outer-html
                // Check the origin of the data! 
                const message = event.data ? JSON.parse(event.data) : {};
                if (message['type'] == 'sl-map-message') {
                    const msg = message;
                    let messageName = msg.name;
                    const messageNameParts = msg.name.split('-');
                    messageName = '';
                    for (const part of messageNameParts) {
                        messageName += part.slice(0, 1).toUpperCase() + part.slice(1);
                    }
                    if (wiindow['Handle' + messageName]) {
                        window['Handle' + messageName](message);
                        return;
                    }

                }
            }, false);

        }

        function HandleRegisterMap(message) {
            if (message.name != 'register-map') return;
            mapFrameId = message.src;
            mapRegistered = true;
            _storedMessages.unshift({
                messageName: 'map-registered',
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
                const msgInfo = storedMessages.shift();
                if (!this.id || !this.endPoint) {
                    storedMessages.unshift(msgInfo);
                    return;
                }
                const message = MakeMessage(msgInfo.messageName, msgInfo.payload);
                console.log('sending message', message);
                frameRef.contentWindow.postMessage(JSON.stringify(message), '*');
                window.requestAnimationFrame(() => {
                    if (this._storedMessages.length > 0) {
                        this.SendMessage();
                    }
                })
            }
        }
        messages = [];

        function MakeMessage(name, content) {
            if (!this.id || !this.endPoint) return;
            const message = {
                name: (name),
                content: content,
                src: mapFrameId,
                url: window.location.href,
                type: 'sl-map-message'
            }
            return message
        }

    });
</script>
<iframe id="selectorFrame"></iframe>