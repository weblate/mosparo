//const $ = require('jquery');
import jQuery from 'jquery';

let collectionToggleRemoveButton = function (list) {
    if (list.find('li').length > 1) {
        list.find('.remove-item-button').prop('disabled', false);
    } else {
        list.find('.remove-item-button').prop('disabled', true);
    }
};

let collectionGetRandomHash = function () {
    return Math.random().toString().substring(2, 16);
}

let updateMultipleCssVariable = function (variables, value, type) {
    for (let ind in variables) {
        updateCssVariable(variables[ind], value, type);
    }
};

let updateCssVariable = function (variableName, value, type) {
    if (type === 'color' && !value) {
        value = 'transparent';
    } else if (type === 'number') {
        value = value + 'px';
    } else if (type === 'value-with-unit' && typeof value === 'object') {
        value = value.value + value.unit;
    }

    document.documentElement.style.setProperty(variableName, value);
};

let getRandomUuid = function ()
{
    if (crypto.randomUUID) {
        return crypto.randomUUID();
    }

    // This should not be used but in case someone is running mosparo in a http context,
    // we cannot use crypto.randomUUID() to get a random UUID. This faked id will later
    // be replaced in the backend.
    let number = new Uint32Array(1);
    crypto.getRandomValues(number);

    return 'dead-' + number[0];
}

window.collectionToggleRemoveButton = collectionToggleRemoveButton;
window.collectionGetRandomHash = collectionGetRandomHash;
window.updateCssVariable = updateCssVariable;
window.updateMultipleCssVariable = updateMultipleCssVariable;
window.getRandomUuid = getRandomUuid;

jQuery(document).ready(function () {
    jQuery('.collection-widget.add-allowed .add-item-button').click(function () {
        let collectionObj = jQuery(this).parents('.collection-widget');
        let list = collectionObj.find('.collection-list');
        let newWidget = list.attr('data-prototype');

        newWidget = newWidget.replace(/__name__/g, collectionGetRandomHash());

        let newElem = jQuery(list.attr('data-widget-tags'));
        let containerEl = newElem;
        if (newElem.find('.input-group').length > 0) {
            containerEl = newElem.find('.input-group');
        }
        containerEl.append(newWidget);
        containerEl.append(jQuery('<button></button>').attr('type', 'button').addClass('btn btn-danger btn-icon-only remove-item-button').html('<i class="ti ti-circle-minus"></i>'));
        newElem.appendTo(list);

        collectionToggleRemoveButton(list);
    });

    jQuery('.collection-widget.add-allowed').each(function () {
        let list = jQuery(this).find('.collection-list');

        if (list.find('li').length === 0) {
            jQuery(this).find('.add-item-button').trigger('click');
        }

        collectionToggleRemoveButton(list);
    });

    jQuery('.collection-widget.remove-allowed').on('click', '.remove-item-button', function () {
        let list = jQuery(this).parents('.collection-list');

        if (list.find('li').length > 1) {
            jQuery(this).parents('li').remove();
        }

        collectionToggleRemoveButton(list);

        list.trigger('collection-item-removed');
    });

    jQuery('.card-field-switch').on('change', function () {
        let cardBody = jQuery(this).parents('.card-body');
        let fields = cardBody.find('input, textarea, select').not(jQuery(this)).not('.always-disabled');
        let status = jQuery(this).is(':checked');
        let disabled = jQuery(this).is(':disabled');

        if (status && !disabled) {
            fields.prop('disabled', false);
        } else {
            fields.prop('disabled', true);
        }

        cardBody.find('.sub-card-field-switch').trigger('change');
    }).trigger('change');

    jQuery('.sub-card-field-switch').on('change', function () {
        let cardBody = jQuery(this).parents('.sub-card-body');
        let fields = cardBody.find('input, textarea, select').not(jQuery(this)).not('.always-disabled');
        let status = jQuery(this).is(':checked');
        let disabled = jQuery(this).is(':disabled');

        if (status && !disabled) {
            fields.prop('disabled', false);
        } else {
            fields.prop('disabled', true);
        }
    }).trigger('change');

    jQuery('.full-card-field-switch').on('change', function () {
        let cardBody = jQuery(this).parents('.card').find('.card-body');
        let fields = cardBody.find('input, textarea, select').not('.always-disabled');
        let status = jQuery(this).is(':checked');

        if (status) {
            fields.prop('disabled', false);
        } else {
            fields.prop('disabled', true);
        }

        cardBody.find('.card-field-switch').trigger('change');
    }).trigger('change');

    let updateVariable = function (el, value, type) {
        if (el.data('variable')) {
            let variableName = el.data('variable');

            if (type === 'checkbox') {
                if (el.is(':checked') && !el.prop('readonly')) {
                    value = el.data('variable-value');
                } else {
                    value = el.data('disabled-variable-value');
                }
            }

            updateCssVariable(variableName, value, type);
        }
    };
    jQuery('input.colorpicker').wrap('<div class="colorpicker-container"></div>').each(function () {
        let allowEmpty = jQuery(this).data('colorpicker-allow-empty');
        if (allowEmpty == null) {
            allowEmpty = true;
        }

        let showAlpha = jQuery(this).data('colorpicker-allow-alpha-value');
        if (showAlpha == null) {
            showAlpha = true;
        }

        jQuery(this).spectrum({
            preferredFormat: "rgb",
            allowEmpty: allowEmpty,
            showInitial: true,
            showButtons: false,
            showAlpha: showAlpha,
            clickoutFiresChange: true,
            move: function (color) {
                jQuery(this).val(color);

                updateVariable(jQuery(this), color, 'color');
                jQuery(this).trigger('color-change');
            }
        });
    }).on('change', function () {
        jQuery(this).spectrum('set', jQuery(this).val());

        updateVariable(jQuery(this), jQuery(this).val(), 'color');
        jQuery(this).trigger('color-change');
    });

    jQuery('input[data-variable!=""]:not(.colorpicker)').change(function () {
        let type = 'number';
        let val = jQuery(this).val();

        if (jQuery(this).is('input[type="checkbox"]')) {
            type = 'checkbox';
        }

        updateVariable(jQuery(this), val, type);
    });

    jQuery('input[data-variable!=""][data-variable]').each(function () {
        let type = 'number';
        let val = jQuery(this).val();

        if (jQuery(this).hasClass('colorpicker')) {
            type = 'color';
        } else if (jQuery(this).is('input[type="checkbox"]')) {
            type = 'checkbox';
        }

        updateVariable(jQuery(this), val, type);
    });

    jQuery('.value-with-unit-widget[data-variable!=""][data-variable]').each(function () {
        let el = jQuery(this);

        let getValue = function () {
            let value = el.find('input').val();
            let unit = el.find('select').val();

            return value + unit;
        }

        el.find('input, select').change(function (ev) {
            if (jQuery(ev.target).is('select')) {
                let input = el.find('input');
                let oldUnit = input.data('unit');

                if (oldUnit === 'px' && jQuery(this).val() !== 'px') {
                    // Simple px to rem calculation
                    input.val(input.val() / 16);
                } else if (jQuery(this).val() === 'px') {
                    // Simple rem to px calculation
                    input.val(input.val() * 16);
                }

                input.data('unit', jQuery(this).val());
            }

            updateVariable(el, getValue(), 'value-with-unit');
        });

        jQuery(this).find('input').data('unit', jQuery(this).find('select').val());

        updateVariable(el, getValue(), 'value-with-unit');
    });

    jQuery('.btn-copy-input-value').click(function () {
        let button = jQuery(this);
        let inputGroup = jQuery(this).parents('.input-group');
        if (inputGroup.length === 0) {
            return;
        }

        button.removeClass('text-success text-danger').find('i').addClass('ti-copy').removeClass('ti-clipboard-check ti-clipboard-x');

        let inputField = inputGroup.find('input');

        navigator.clipboard.writeText(inputField.val()).then(function () {
            button.addClass('text-success').find('i').removeClass('ti-copy').addClass('ti-clipboard-check');
        }, function () {
            button.addClass('text-success').find('i').removeClass('ti-copy').addClass('ti-clipboard-x');
        });
    });

    var changeTimeout = null;
    var changeDirection = '';
    var changeInputField = null;
    var changeIntervalTime = 500;
    var changeValueCallback = function () {
        clearTimeout(changeTimeout);
        changeTimeout = null;

        if (changeDirection === '' || changeInputField === null) {
            return;
        }

        let val = parseInt(changeInputField.val());

        let min = 0;
        if (changeInputField.attr('min')) {
            min = parseInt(changeInputField.attr('min'));
        }

        let max = 0;
        if (changeInputField.attr('max')) {
            max = parseInt(changeInputField.attr('max'));
        }

        if (changeDirection === '-') {
            val -= 1;
        } else if (changeDirection === '+') {
            val += 1;
        }

        if (val < min) {
            val = min;
        }

        if (max > 0 && val > max) {
            val = max;
        }

        changeInputField.val(val).trigger('change');

        changeIntervalTime = changeIntervalTime * 0.85;
        if (changeIntervalTime < 50) {
            changeIntervalTime = 50;
        }

        changeTimeout = setTimeout(changeValueCallback, changeIntervalTime);
    }

    jQuery('.btn-decrease-value, .btn-increase-value').on('mousedown mouseup', function (ev) {
        if (ev.type === 'mousedown') {
            if (changeDirection !== '') {
                return;
            }

            if (jQuery(this).hasClass('btn-decrease-value')) {
                changeDirection = '-';
            } else if (jQuery(this).hasClass('btn-increase-value')) {
                changeDirection = '+';
            }

            changeInputField = jQuery(this).parents('.input-group').find('input');
            changeIntervalTime = 400;
            changeValueCallback();
        } else if (ev.type === 'mouseup') {
            clearTimeout(changeTimeout);
            changeTimeout = null;

            changeDirection = '';
            changeInputField = null;

            ev.preventDefault();
            return false;
        }
    });

    jQuery('.btn-decrease-value').parents('.input-group').find('input').on('keydown keyup', function (ev) {
        if (ev.keyCode !== 37 && ev.keyCode !== 38 && ev.keyCode !== 39 && ev.keyCode !== 40) {
            return;
        }

        if (ev.type === 'keydown') {
            if (changeDirection !== '') {
                return;
            }

            changeDirection = '';
            if (ev.keyCode === 37 || ev.keyCode === 40) {
                changeDirection = '-';
            } else if (ev.keyCode === 38 || ev.keyCode === 39) {
                changeDirection = '+';
            }

            changeInputField = jQuery(this);
            changeIntervalTime = 500;
            changeValueCallback();
        } else if (ev.type === 'keyup') {
            changeDirection = '';
            changeInputField = null;
        }

        ev.preventDefault();
        return false;
    }).attr("autocomplete", "off");

    jQuery('.input-with-clear-button').each(function () {
        let container = jQuery(this);
        let input = container.find('input');
        let link = container.find('a');
        input.on('keyup change', function () {
            if (jQuery(this).val() === '') {
                link.addClass('invisible');
            } else {
                link.removeClass('invisible');
            }
        }).trigger('change');

        link.click(function (ev) {
            if (link.attr('href') === '#') {
                input.val('').trigger('change');
                ev.preventDefault();
            }
        });
    });
});
