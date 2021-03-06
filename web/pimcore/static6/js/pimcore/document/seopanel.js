/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

pimcore.registerNS("pimcore.document.seopanel");
pimcore.document.seopanel = Class.create({

    initialize: function () {
        this.getTabPanel();
    },


    activate: function () {
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.setActiveItem("pimcore_document_seopanel");
    },

    getTabPanel: function () {

        if (!this.panel) {
            this.panel = new Ext.Panel({
                id: "pimcore_document_seopanel",
                title: t("seo_document_editor"),
                iconCls: "pimcore_icon_seo",
                border: false,
                layout: "fit",
                closable:true,
                items: []
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem("pimcore_document_seopanel");

            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("document_seopanel");
            }.bind(this));

            pimcore.layout.refresh();

            Ext.Ajax.request({
                url: "/admin/document/seopanel-tree-root",
                success: function (response) {
                    var res = Ext.decode(response.responseText);
                    if(res["id"]) {
                        this.getTreeGrid(res);
                    }
                }.bind(this)
            });
        }

        return this.panel;
    },

    getTreeGrid: function (rootNodeConfig) {


        rootNodeConfig.nodeType = "async";
        rootNodeConfig.text = "home";
        rootNodeConfig.iconCls = "pimcore_icon_home";
        rootNodeConfig.expanded = true;
        rootNodeConfig.attributes = {};

        var columns = [{
            xtype: 'treecolumn',
            text: t("name"),
            dataIndex: 'text',
            width: 300
        },{
            text: t("pretty_url"),
            dataIndex: 'prettyUrl',
            width: 180
        },{
            text: t("title"),
            dataIndex: 'title',
            width: 230
        },{
            text: t("length"),
            dataIndex: 'title_length',
            width: 50
        },{
            text: t("description"),
            dataIndex: 'description',
            width: 400
        },{
            text: t("length"),
            dataIndex: 'description_length',
            width: 50
        },{
            text: "H1",
            dataIndex: 'h1',
            width: 25
        },{
            text: t("h1_text"),
            dataIndex: 'h1_text',
            width: 300
        },{
            text: "H2-5",
            dataIndex: 'hx',
            width: 35
        },{
            text: t("images_with_alt"),
            dataIndex: 'imgwithalt',
            width: 120
        },{
            text: t("images_without_alt"),
            dataIndex: 'imgwithoutalt',
            width: 120
        },{
            text: t("links"),
            dataIndex: 'links',
            width: 50
        },{
            text: t("external_links"),
            dataIndex: 'externallinks',
            width: 90
        }];

        var store = Ext.create('Ext.data.TreeStore', {
            proxy: {
                type: 'ajax',
                url: '/admin/document/seopanel-tree'
            }
        });

        var tree = Ext.create('Ext.tree.Panel', {
                store: store,
                columns: columns,
                enableSort: false,
                animate: false,
                rootVisible: true,
                root: rootNodeConfig,
                border: false,
                lines: true,
                cls: "pimcore_document_seo_tree",
                listeners: {
                    "itemclick": this.openEditPanel.bind(this),
                    "itemcontextmenu": this.onRightClick.bind(this),
                    'render': function () {
                        this.getRootNode().expand();
                    }
                }
            }
        );

        this.panel.add(tree);
        this.panel.updateLayout();
    },

    onRightClick: function (tree, record, item, index, e, eOpts ) {
        tree.select();

        var menu = new Ext.menu.Menu();
        menu.add([{
            text: t("open"),
            iconCls: "pimcore_icon_edit",
            handler: pimcore.helpers.openDocument.bind(window, record.data.id, record.data.type)
        },{
            text: t('reload'),
            iconCls: "pimcore_icon_reload",
            handler: function (tree) {
                tree.getStore().reload();
            }.bind(this, tree)
        },{
            text: t('open_in_new_window'),
            iconCls: "pimcore_icon_open",
            handler: function (record) {
                window.open(record.data.path);
            }.bind(this, record)
        }]);

        e.stopEvent();
        menu.showAt(e.pageX, e.pageY);
    },

    openEditPanel: function (tree, record, item, index, e, eOpts ) {

        if(record.data.type != "page") {
            return;
        }

        if(this.editWindow) {
            delete this.editWindow;
        }
        if(this.formPanel) {
            delete this.formPanel;
        }

        this.formPanel = new Ext.form.FormPanel({
            bodyStyle: "padding:10px;",
            items: [{
                xtype: "textfield",
                fieldLabel: t("title") + " (" + record.data.title.length + ")",
                name: "title",
                value: record.data.title,
                width: 450,
                enableKeyEvents: true,
                listeners: {
                    "keyup": function (el) {
                        el.setFieldLabel(t("title") + " (" + el.getValue().length + "):");
                    }
                }
            }, {
                xtype: "textfield",
                fieldLabel: t("pretty_url"),
                name: "prettyUrl",
                value: record.data.prettyUrl,
                width: 450
            }, {
                xtype: "textarea",
                fieldLabel: t("description") + " (" + record.data.description.length + ")",
                name: "description",
                value: record.data.description,
                width: 450,
                enableKeyEvents: true,
                listeners: {
                    "keyup": function (el) {
                        el.setFieldLabel(t("description") + " (" + el.getValue().length + "):");
                    }
                }
            }, {
                xtype: "hidden",
                name: "id",
                value: record.data.id
            }]
        });

        this.editWindow = new Ext.Window({
            modal: true,
            width: 500,
            height: 290,
            closable: true,
            items: [this.formPanel],
            buttons: [{
                text: t("save"),
                iconCls: "pimcore_icon_accept",
                handler: this.save.bind(this, tree, record)
            }]
        });

        this.editWindow.show();
    },

    save: function (tree, record) {

        var values = this.formPanel.getForm().getFieldValues();
        this.editWindow.close();

        Ext.Ajax.request({
            url: "/admin/document/update",
            method: "post",
            params: values,
            success: function (node) {
                if (values.id == 1) {
                    Ext.Ajax.request({
                        url: "/admin/document/seopanel-tree-root",
                        success: function (response) {
                            var cfg = Ext.decode(response.responseText);
                            if(cfg.id) { // We are the root node
                                var rootNode = tree.getStore().getRootNode();
                                rootNode.set(cfg); // set the changes as set for the document
                                rootNode.set({ // reset root node stuff
                                    text: "home",
                                    iconCls:  "pimcore_icon_home",
                                    expanded: true
                                });
                                rootNode.commit(true); // Tell the model that everything's ok without telling the store
                                tree.refresh(); // refresh the tree t
                            }
                        }.bind(tree)
                    });
                } else {
                    tree.getStore().load();
                }
            }.bind(this, tree, record)
        });
    }

});
