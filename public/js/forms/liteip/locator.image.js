var Script = function () {
    var self = $('#locator-container');

    var width = self.innerWidth(),
        imgSetup =  {
            width: 0,
            heigth: 0,
            src: '',
            zoom: 1,
            devices: [],
            mode: 1,
            fDevices: []
        };

    var RADIUS_DESELECTED = 5,
        RADIUS_SELECTED = 8;

    var rtime,
        timeout = false,
        RESIZE_DELTA = 1000;

    var selectedDevice = false;

    var stage = new Konva.Stage({
        container: 'locator-container',   // id of container <div>
        width: width,
        height: 500
    });

    // then create layers
    var bgLayer = new Konva.Layer();
    var deviceLayer = new Konva.Layer();

    var backgroundImage = new Konva.Image({
        x: 0,
        y: 0
    });

    // add the shape to the bgLayer
    bgLayer.add(backgroundImage);

    // add the bgLayer to the stage
    stage.add(bgLayer);
    stage.add(deviceLayer);

    var imageObj = new Image();
    imageObj.onload = function() {
        backgroundImage.setImage(imageObj);
        bgLayer.draw();
        createDevices();
    };



    $('#sel-device-view-mode').on('change', function() {
        imgSetup.mode = parseInt($(this).val());
        redrawDevices();
    });

    $('#btn-show-serials').on('click', function() {
        var serials = (($('#inp-show-serials').val()).replace(/[,\r\n ]+/g,' ').replace(/[ ]+/g, ' ').trim()).split(" ");

        if (!serials) {
            return;
        }

        imgSetup.mode = 5;
        imgSetup.fDevices = serials;
        $('#sel-device-view-mode').val(imgSetup.mode);

        redrawDevices();
    });

    self.on('addSerial', function (event, serial) {
        var serials = $('#inp-show-serials').val() + " " + serial;
        $('#inp-show-serials').val(serials.trim());
        $('#btn-show-serials').trigger('click');
    });

    /**
     * exposed drawDevices event
     */
    self.on('drawDevices', function (event, data) {
        if (!data) {
            return;
        }

        imgSetup.width = data.width;
        imgSetup.height = data.height;

        $('#sel-device-view-mode').val(1); // reset view mode

        // prepare canvas and device load
        resizeCanvas();
        removeAllDevices();

        imageObj.src = '/playground/liteipdrawingimage/?drawingId=' + data.drawingID;

        imageObj.devices = data.devices;
    });

    /**
     * remove all devices from canvas
     */
    function removeAllDevices() {
        var devices = deviceLayer.find('.device-group');

        if (!devices || devices.length < 1) {
            return;
        }

        for(var i in devices) {
            if (!(devices[i] instanceof Konva.Group)) {
                continue;
            }

            devices[i].destroy();
        }

        deviceLayer.draw();
    }

    /**
     * redraw the device
     */
    function redrawDevices() {
        var devices = deviceLayer.find('.device-group');

        if (!devices || devices.length < 1) {
            return;
        }

        for(var i in devices) {
            if (!(devices[i] instanceof Konva.Group)) {
                continue;
            }

            devices[i].attrs.redrawFunc();
        }
        deviceLayer.draw();
    }

    /**
     * create devices from imgObj
     */
    function createDevices() {
        for (var i in imageObj.devices) {
            addDevice(
                imageObj.devices[i][0],
                imageObj.devices[i][1],
                imageObj.devices[i][2],
                imageObj.devices[i][3],
                imageObj.devices[i][4],
                !!imageObj.devices[i][5] ? new Date(imageObj.devices[i][5]) : false,
                imageObj.devices[i][6],
                imageObj.devices[i][7]
            );
        }
        deviceLayer.draw();
    }

    /**
     * add a device to deviceLayer
     * @param id
     * @param serial
     * @param x
     * @param y
     */
    function addDevice(id, serial, emergency, fault, status, lastChecked, x, y) {
        var circle = new Konva.Circle({
            radius: RADIUS_DESELECTED,
            fill: !!fault ? 'red' : '#74B749',
            stroke: 'black',
            strokeWidth: 1
        });

        var group = new Konva.Group({
            draggable: false,
            height: self.strokeWidth,
            name: 'device-group',
            id: 'device-' + id,
            x: Math.ceil(x * imgSetup.zoom),
            y: Math.ceil(y * imgSetup.zoom),
            redrawFunc: function () {
                group.x(Math.ceil(group.custom.x * imgSetup.zoom));
                group.y(Math.ceil(group.custom.y * imgSetup.zoom));

                switch (imgSetup.mode) {
                    case 2: //emergency only
                        group.visible(!!emergency);
                        group.attrs.deselectFunc();
                        break;
                    case 3: //non-emergency only
                        group.visible(!emergency);
                        group.attrs.deselectFunc();
                        break;
                    case 4: //failing only
                        group.visible(!!fault);
                        group.attrs.deselectFunc();
                        break;
                    case 5: //bespoke list
                        group.visible(imgSetup.fDevices.indexOf("" + group.custom.serial) !== -1);
                        group.attrs.deselectFunc();
                        break;
                    default:
                        group.visible(true);
                        break;
                }

            },
            selectFunc: function() {
                circle.setRadius(RADIUS_SELECTED);
            },
            deselectFunc: function() {
                circle.setRadius(RADIUS_DESELECTED);
            }
        });

        group.custom = {
            id: id,
            serial: serial,
            x: x,
            y: y,
            emergency: !!emergency,
            fault: !!fault,
            status: status,
            lastChecked: lastChecked
        };

        group.add(circle);

        group.on('mouseover', function() {
            group.attrs.selectFunc();
            deviceLayer.draw();
            self.css('cursor', 'pointer');
            setStatus(group.custom);
        }).on('mouseout', function() {
            if (selectedDevice === false || selectedDevice.id !== group.custom.id) {
                group.attrs.deselectFunc();
                deviceLayer.draw();
            }
            self.css('cursor', 'default');
            setStatus(selectedDevice);
        }).on('click', function (){
            if (selectedDevice !== false) {
                if (selectedDevice.id === group.custom.id) {
                    selectedDevice = false;
                    return;
                } else {
                    var device = deviceLayer.find('#device-' +  selectedDevice.id)[0];

                    if (device instanceof Konva.Group) {
                        device.attrs.deselectFunc();
                        deviceLayer.draw();
                    }
                }
            }

            selectedDevice = group.custom;
        });

        deviceLayer.add(group);
    }

    function setStatus(custom) {
        $('#device-id').text(!custom ? '-' : custom.id);
        $('#device-serial').text(!custom ? '-' : custom.serial);
        $('#device-emergency').text(!custom ? '-' : (!!custom.emergency ? 'Yes' : 'No'));
        $('#device-status').text(!custom ? '-' : custom.status);
        $('#device-checked').text(!custom ? '-' : (!custom.lastChecked ? '-' : moment(custom.lastChecked).format('MMMM Do YYYY h:mm a')));
    }

    /**
     * resize canvas and update associated attributes
     */
    function resizeCanvas() {
        imgSetup.zoom = !!imgSetup.width ? width / imgSetup.width : 1;

        backgroundImage.setWidth(imgSetup.width * imgSetup.zoom);
        backgroundImage.setHeight(Math.ceil(imgSetup.height * imgSetup.zoom));
        stage.setWidth(width);
        stage.setHeight(Math.ceil(imgSetup.height * imgSetup.zoom));
        bgLayer.draw();
    }

    /**
     * resize the canvas callback
     */
    function resizeCallback() {
        if (new Date() - rtime < RESIZE_DELTA) {
            setTimeout(resizeCallback, RESIZE_DELTA);
        } else {
            timeout = false;
            width = self.innerWidth();
            resizeCanvas();
            redrawDevices();
        }
    }

    $(window).resize(function() {
        rtime = new Date();
        if (timeout === false) {
            timeout = true;
            setTimeout(resizeCallback, RESIZE_DELTA);
        }
    });




}();