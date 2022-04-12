$.extend({postJSON: function (url, data, callback) {
        return jQuery.post(url, data, callback, "json");
    }});
(function ($, window, document, undefined) {
    $.fn.dataSelector = function (options) {
        var settings = $.extend({
            'type': 'layer',
            'default': 1,
            'tag': '',
            'extend': '',
            'sources': [1, 2, 3, 4],
            'source': {"layer": {1: ["My Layers", 1, function () {
                            return "./?do=wapi.views&type=mine&object=layer&format=json";
                        }],
                    2: ["Bookmarked Layers", 1, function () {
                            return "./?do=wapi.views&type=marks&object=layer&format=json";
                        }],
                    3: ["Available to me", 2, function () {
                            return "./?do=wapi.views&type=owners&object=layer&format=json";
                        }, function (id) {
                            return "./?do=wapi.views&type=owner&object=layer&owner=" + id + "&format=json";
                        }, [{id: "-1", name: "All Shared"}]],
                    4: ["Groups", 2, function () {
                            return "./?do=wapi.views&type=groups&object=layer&format=json";
                        }, function (id) {
                            return "./?do=wapi.views&type=group&object=layer&id=" + id + "&format=json";
                        }, {}],
                    5: ["Tag", 3, function () {
                            return "./?do=wapi.views&type=tag&object=layer&format=json";
                        }, function (id) {
                            return "./?do=wapi.views&type=tag&object=layer&tag=" + id + "&format=json";
                        }, {}]
                },
                "project": {1: ["My Maps", 1, function () {
                            return "./?do=wapi.views&type=mine&object=project&format=json";
                        }],
                    2: ["Bookmarked Maps", 1, function () {
                            return "./?do=wapi.views&type=marks&object=project&format=json";
                        }],
                    3: ["Available to me", 2, function () {
                            return "./?do=wapi.views&type=owners&object=project&format=json";
                        }, function (id) {
                            return "./?do=wapi.views&type=owner&object=project&owner=" + id + "&format=json";
                        }, [{id: "-1", name: "All Shared"}]],
                    4: ["Groups", 2, function () {
                            return "./?do=wapi.views&type=groups&object=project&format=json";
                        }, function (id) {
                            return "./?do=wapi.views&type=group&object=project&id=" + id + "&format=json";
                        }, {}],
                    5: ["Tag", 3, function () {
                            return "./?do=wapi.views&type=tag&object=project&format=json";
                        }, function (id) {
                            return "./?do=wapi.views&type=tag&object=project&tag=" + id + "&format=json";
                        }, {}]
                },
                "contact": {1: ["My Contacts", 1, function () {
                            return "./?do=wapi.contact.views&type=mine&format=json";
                        }],
                    2: ["Group Members", 2, function () {
                            return "./?do=wapi.contact.views&type=groups&format=json";
                        }, function (id) {
                            return "./?do=wapi.contact.views&type=group&id=" + id + "&format=json";
                        }, {}],
                    3: ["Added Me", 1, function () {
                            return "./?do=wapi.contact.views&type=others&format=json";
                        }],
                    4: ["Not Added", 1, function () {
                            return "./?do=wapi.contact.views&type=everyoneelse&format=json";
                        }],
                    5: ["Tag", 3, function () {
                            return "./?do=wapi.contact.views&type=tag&format=json";
                        }, function (id) {
                            return "./?do=wapi.contact.views&type=tag&tag=" + id + "&format=json";
                        }, {}]
                },
                "group": {1: ["Participating", 1, function () {
                            return "./?do=wapi.group.views&type=mine&format=json";
                        }],
                    2: ["I Moderate", 1, function () {
                            return "./?do=wapi.group.views&type=imoderate&format=json";
                        }],
                    3: ["I Am In", 1, function () {
                            return "./?do=wapi.group.views&type=iamin&format=json";
                        }],
                    4: ["Open", 1, function () {
                            return "./?do=wapi.group.views&type=open&format=json";
                        }],
                    5: ["Invite Only", 1, function () {
                            return "./?do=wapi.group.views&type=invite&format=json";
                        }],
                    6: ["Tag", 3, function () {
                            return "./?do=wapi.group.views&type=tag&format=json";
                        }, function (id) {
                            return "./?do=wapi.group.views&type=tag&tag=" + id + "&format=json";
                        }, {}]
                },
                "groupmine": {
                    1: ["I Moderate", 1, function () {
                            return "./?do=wapi.group.views&type=imoderate&format=json";
                        }],
                    2: ["I Am In", 1, function () {
                            return "./?do=wapi.group.views&type=iamin&format=json";
                        }]
                }
            },
            'filter': false,
            'loader': '<span class="loader-container"><img id="loader" src="media/images/ajax-loader.gif" style="background-color:none;"/></span>'
        }, options);

        return this.each(function () {
            var data = $(this).data('dataSelector');
            if (!data) {
                //-- Build First Selector
                var selector = $('<select class="sel form-select custom-select"></select>').appendTo($(this));
                var selector2 = $('<select class="sel2 form-select custom-select"></select>').appendTo($(this));
                var textbox = $('<input type="text" class="form-control seltext" />').appendTo($(this));
                var loader = $(settings["loader"]).appendTo($(this));
                var jsonQueue = $(this).jsonQueue();
                $(this).data('dataSelector', {
                    target: $(this),
                    selector: selector,
                    selector2: selector2,
                    textbox: textbox,
                    loader: loader,
                    jsonQueue: jsonQueue
                });



                function retrieveData(element, info, id) {
                    var data = element.data('dataSelector');
                    var url;
                    if (info[1] == 1) {
                        url = info[2]();
                    } else {
                        if (id == null) {
                            data.loader.css('visibility', 'hidden');
                            return false;
                        }
                        url = info[3](id);
                    }
                    url = url + settings['extend'];
                    data.jsonQueue.nextQueue(url, function (jsonData, context) {
                        var data = context.data('dataSelector');
                        context.trigger("update", jsonData);
                        data.loader.css('visibility', 'hidden');
                    },
                            function (jsonData) {}, element);
                }



                var option;
                $.each(settings['source'][settings['type']], function (key, value) {
                    option = $('<option></option>').text(value[0]).val(key).appendTo(selector);
                    if (settings['default'] == key)
                        option.attr('selected', 'selected');
                });
                selector.change(function (e) {
                    $(this).parent().trigger("loading");
                    oldval = '';
                    var id = $(this).val();
                    var info = settings['source'][settings['type']][id];
                    var data = $(this).parent().data('dataSelector');
                    data.selector2.empty().css('display', 'none');
                    data.textbox.empty().css('display', 'none');
                    data.loader.css('visibility', 'visible');
                    if (info[1] == 1) {
                        retrieveData($(this).parent(), info);
                    } else if (info[1] == 2) {
                        //-- Add extra options
                        $.each(info[4], function (i, row) {
                            $('<option></option>').text(row.name).val(row.id).appendTo(data.selector2);
                        });
                        //-- Get the real list of option
                        $.getJSON(info[2](), function (jsonData) {

                            var firstNode = $('<option>Select a group</option>').val(null);
                            if (selector.val() == 2)
                                firstNode.appendTo(data.selector2);
                            var foundSel = false;
                            $.each(jsonData.view, function (i, row) {
                                var isSel = (row.id == settings['groupId']);
                                var node = $('<option></option>').text(row.name).val(row.id).appendTo(data.selector2);

                                if (isSel) {
                                    foundSel = true;
                                    node.attr('selected', 'selected');
                                }
                            });
                            if (selector.val() == 2) {
                                if (!foundSel)
                                    firstNode.attr('selected', 'selected');
                            }
                            data.selector2.css('display', 'inline').change();
                            if (options)
                                options['groupId'] = null;
                        }).error(function (a, b, c) {
                            alert(b + '  : ' + c);
                        });
                    } else {
                        data.textbox.css('display', 'inline').change();
                        data.loader.css('visibility', 'hidden');
                        data.textbox.keyup();
                    }
                }).change();

                selector2.change(function (e) {
                    $(this).parent().trigger("loading");
                    var data = $(this).parent().data('dataSelector');
                    data.loader.css('visibility', 'visible');


                    var info = settings['source'][settings['type']][data.selector.val()];
                    retrieveData($(this).parent(), info, $(this).val());

                });
                var oldval = '';
                textbox.keyup(function (e) {
                    if (oldval != $(this).val()) {
                        oldval = $(this).val();
                        $(this).parent().trigger("loading");
                        var data = $(this).parent().data('dataSelector');
                        data.loader.css('visibility', 'visible');
                        var info = settings['source'][settings['type']][data.selector.val()];
                        retrieveData($(this).parent(), info, $(this).val());
                    }
                }).val(settings['tag']).keyup();
                selector2.css('display', 'none');
            }
        });
    };
})(jQuery);