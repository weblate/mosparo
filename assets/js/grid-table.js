import jQuery from 'jquery';

function GridTable(gridEl, breakpoints, options)
{
    let _this = this;
    this.gridEl = gridEl;
    this.breakpoints = breakpoints;
    this.mqls = [];
    this.defaultOptions = {};
    this.options = {...this.defaultOptions, ...options};

    this.init = function () {
        for (const breakpoint of this.breakpoints) {
            const mql = window.matchMedia(breakpoint[0]);
            const template = breakpoint[1];

            mql.addEventListener('change', function (e) {
                if (e.matches) {
                    _this.adjustTemplate(template);
                    _this.hideColumns(template);
                }
            });

            //this.mqls.push(mql);

            if (mql.matches) {
                mql.dispatchEvent(new MediaQueryListEvent('change', {
                    media: mql.media,
                    matches: true,
                }));
            }
        }

        if ('additionalClassesBreakpoints' in this.options && this.options.additionalClassesBreakpoints.length > 0) {
            for (const acBreakpoint of this.options.additionalClassesBreakpoints) {
                const mql = window.matchMedia(acBreakpoint[0]);
                const classes = acBreakpoint[1];

                mql.addEventListener('change', function (e) {
                    if (e.matches) {
                        _this.gridEl.addClass(classes);
                    } else {
                        _this.gridEl.removeClass(classes);
                    }
                });

                mql.dispatchEvent(new MediaQueryListEvent('change', {
                    media: mql.media,
                    matches: mql.matches,
                }));
            }
        }

        if ('headerGroupBreakpoints' in this.options && this.options.headerGroupBreakpoints.length > 0) {
            const headerGroupRow = this.gridEl.find('.grid-table-header .header-groups-row');
            for (const [idx, hgBreakpoint] of this.options.headerGroupBreakpoints.entries()) {
                const mql = window.matchMedia(hgBreakpoint[0]);
                const columns = hgBreakpoint[1];
                const headerGroupEl = headerGroupRow.find('.grid-table-cell').eq(idx);

                headerGroupEl[0].style.setProperty('grid-column', columns);

                mql.addEventListener('change', function (e) {
                    if (e.matches) {
                        headerGroupEl.show();
                    } else {
                        headerGroupEl.hide();
                    }
                });

                mql.dispatchEvent(new MediaQueryListEvent('change', {
                    media: mql.media,
                    matches: mql.matches,
                }));
            }
        }

        this.gridEl.find('.grid-table-button').on('click', function () {
            let gridRow = jQuery(this).parents('.grid-table-row');

            if (gridRow.hasClass('collapsed-visible')) {
                gridRow.removeClass('collapsed-visible');
                $(this).find('i').addClass('ti-plus').removeClass('ti-minus');
            } else {
                gridRow.addClass('collapsed-visible');
                $(this).find('i').removeClass('ti-plus').addClass('ti-minus');
            }
        });

        this.gridEl.removeClass('loading');
    }

    this.adjustTemplate = function (template) {
        this.gridEl[0].style.setProperty('--grid-table-template', template);
    }

    this.hideColumns = function (template) {
        let numberOfColumns = this.gridEl.find('.grid-table-row:not(.grid-table-header):not(.header-groups-row):first .grid-table-cell').length;
        let numberOfVisibleColumns = template.split(' ').length;

        this.gridEl.find('.grid-table-row .grid-table-cell:nth-child(-n+' + numberOfVisibleColumns + ')').removeClass('collapsed');

        if (numberOfVisibleColumns < numberOfColumns) {
            this.gridEl.find('.grid-table-row .grid-table-cell:nth-child(n+' + (numberOfVisibleColumns + 1) + ')').addClass('collapsed');
            this.gridEl.find('.grid-table-row').addClass('button-visible');
        } else {
            this.gridEl.find('.grid-table-row').removeClass('button-visible');
            this.gridEl.find('.collapsed-visible .grid-table-button .btn').trigger('click');
        }
    }

    this.init();

    return this;
}

window.GridTable = GridTable;
