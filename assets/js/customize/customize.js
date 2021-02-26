import { handleOpen, checkedComponentData } from './customize-modal-template.js';
( function ( $ ) {
    $( document ).ready( function () {
        const customizeBlock = $( '.variations__link.btn-modal' );
        var customizeItemData = {};
        let circleProgressBars = [];
        let lastSelectedComponent;
        let startVariationPrice;
        let componentsJson;
        let currentBlock;
        let currentBlockNumber;
        let currentProductId;
        let isInit = false;

       const toggleSwitch = function () {
            let $this = $(this),
                $par = $this.parent('.toggle-switch');
            let current_toggle_class,
                shadow_toggle_class;

            window.surveyExists = !!$par.hasClass('toggle-switch--on');

            if ( ! window.surveyExists ) {
                current_toggle_class = 'toggle-switch--on';
                shadow_toggle_class = 'toggle-switch--off';
            } else {
                current_toggle_class = 'toggle-switch--off';
                shadow_toggle_class = 'toggle-switch--on';
            }

            $('.' + current_toggle_class).removeClass(current_toggle_class).addClass(shadow_toggle_class);

            let dataInfo = {
                action: 'toggle_change',
                ajax_nonce: settingsCustomizer.ajax_nonce,
                survey_status: window.surveyExists,
            };

            $.ajax({
                type: 'POST',
                url: main.ajaxurl,
                data: dataInfo,
                success: function (response) {
                    customizeItemData = {
                        currentBlock: currentBlockNumber,
                        currentVariationInfo: settingsCustomizer.variation_info,
                        productId: currentProductId,
                        componentData: JSON.parse( response.data ),
                        startVariationPrice: startVariationPrice
                    }

                    $( '#customize-meal-json' ).text( response.data )
                    localStorage.setItem('survey_default', window.surveyExists);
                    handleOpen( customizeItemData );

                    let modalBuilder = $this.closest( '.modal-builder' );
                    let first = $( 'label.option-item', modalBuilder ).first().get(0);
                    first.click();

                },
            });
        };

        customizeBlock.on( 'click', function( e ) {
            e.preventDefault();
            startVariationPrice = $( '.product-card__body .product-card__right bdi' )[0].textContent.replace( '$', '' )
            let $this = $( this );
            componentsJson = jsonData( '#customize-meal-json' );

            // collect data for template
            currentBlock = $this[0].href;
            currentBlockNumber = currentBlock.substr( currentBlock.indexOf( 'pa_part' ), currentBlock.length );
            currentProductId = $( 'main' )[0].id.replace( /[a-zA-Z]*-/g, '' );

            // object with data for template
            customizeItemData = {
                currentBlock: currentBlockNumber,
                currentVariationInfo: settingsCustomizer.variation_info,
                productId: currentProductId,
                componentData: componentsJson,
                startVariationPrice: startVariationPrice
            }

            // render template
            handleOpen( customizeItemData );

            // init chef hats
            let event = new Event('init_ajax_rating');
            document.dispatchEvent( event );

            // open customize modal
            if ( isInit ) {

                let modalBuilder = $('body').find( '.modal-builder#meals-variation-group-' + currentBlockNumber );
                let first = $( 'label.option-item', modalBuilder ).first().get(0);
                first.click();

                $.fancybox.open({
                    src: '#meals-variation-group-' + currentBlockNumber,
                    type: 'inline',
                    touch: false,
                    backFocus: false,
                    afterClose: (instance, slide) => {
                        circleProgressBars.forEach(circleProgressBar => {
                            circleProgressBar.destroy();
                        });

                    },
                });
            }

        } );

        $( 'body' ).on( 'click', 'label.option-item', function( e ) {
            e.preventDefault();

            const $this = $( this );
            const itemDataAttributes = $this.data();
            lastSelectedComponent = $this;

            $this.closest( 'ul' ).find( 'input:checked' )[0].removeAttribute( 'checked' );
            $this.find( 'input[name="moroccan-chicken-stew"]' )[0].setAttribute( 'checked', 'checked' );

            $( '#customizer-footer-title' ).text( itemDataAttributes.name );
            $( '#customizer-footer-description' ).text( itemDataAttributes.desc );

            $( '.nutrition-item .summ_cal' )
                .attr( 'data-value-grams', itemDataAttributes.cal )
                .attr( 'data-value-percent', itemDataAttributes.cal );

            $( '.nutrition-item .summ_carbs' )
                .attr( 'data-value-grams', itemDataAttributes.carbs + ' g' )
                .attr( 'data-value-percent', itemDataAttributes.carbsPercentage );

            $( '.nutrition-item .summ_fat' )
                .attr( 'data-value-grams', itemDataAttributes.fat + ' g' )
                .attr( 'data-value-percent', itemDataAttributes.fatPercentage );

            $( '.nutrition-item .summ_protein' )
                .attr( 'data-value-grams', itemDataAttributes.protein + ' g' )
                .attr( 'data-value-percent', itemDataAttributes.proteinPercentage );

            getVariationLink( $this );

        } )

        // progress bar circle
        const initProgressBarCircle = function () {
            circleProgressBars.length = 0;
            let progressBars = $( 'footer .progress-bar' );
            for ( let progressBar of progressBars ) {
                const valueGrams = progressBar.dataset.valueGrams || 0;
                const valuePercent = +progressBar.dataset.valuePercent || 0;
                const color = progressBar.dataset.color;
                const bar = new ProgressBar.Circle( progressBar, {
                    color: color,
                    strokeWidth: 6,
                    trailWidth: 0,
                    trailColor: '#fff',
                    fill: '#fff',
                    easing: 'easeInOut',
                    duration: 1400,
                    svgStyle: {
                        strokeLinecap: 'round',
                    },
                    text: {
                        className: 'progress-bar__value',
                        style: null,
                    },
                    from: {
                        color: color
                    },
                    to: {
                        color: color
                    },
                    step: ( state, circle ) => {
                        circle.setText(
                            `
                                <span class="progress-bar__value-grams">${ valueGrams }</span>
                                ${ valuePercent ? '<span class="progress-bar__value-percent">' 
                                + ( circle.value() * 100 ).toFixed() + '%</span>' : '' }
                            `
                        );
                    }
                });
                // todo fix .animate `uncaught in promise` on fast clicking
                bar.animate( valuePercent / 100 ).catch(function(state){});
                circleProgressBars.push( bar );
            }
        }

        // get json from DOM
        const jsonData = function ( selector ) {
            try {
                let data = $( selector );

                if ( data.length && data.text() ) {
                    return JSON.parse( data.text() );
                } else {
                    console.error( 'Empty customize components data' );
                    return JSON.parse( '[]' );
                }
            } catch ( err ) {
                console.error( 'Error customize components data: ' + err );
                return JSON.parse( '[]' );
            }
        };

        const getVariationLink = function ( currentComponent ) {
            const netsuitId = currentComponent.data( 'component-id' );
            let formData = new FormData()
            formData.append( 'action', 'get_variation_link' )
            formData.append( 'nonce', settingsCustomizer.ajax_nonce )
            formData.append( 'newCheckedComponentId', netsuitId )
            formData.append( 'oldCheckedComponentId', checkedComponentData.netsuit_id )
            formData.append( 'allComponents', [
                customizeItemData.currentVariationInfo.var_attributes[0].term_id,
                customizeItemData.currentVariationInfo.var_attributes[1].term_id,
                customizeItemData.currentVariationInfo.var_attributes[2].term_id,
            ] )
            $.ajax( {
                url: settingsCustomizer.ajax_url,
                method: 'POST',
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $( '.builder-footer__button' ).attr('disabled','disabled');
                },
                success: function ( response ) {
                    currentComponent.attr( 'data-link', response.data.link );
                    currentComponent.attr( 'data-price', Number( response.data.price ).toFixed( 2 ) );
                    $( '.builder-footer__button > span' ).text( Number( response.data.price ).toFixed( 2 ) );
                    $( '.builder-footer__button' ).removeAttr('disabled');
                },
                error: function ( response ) {
                    console.log( response );
                }
            } )
        }

        $( 'body' ).on( 'click', 'footer .builder-footer__button', function ( e ) {
            e.preventDefault();

            if ( typeof lastSelectedComponent === 'undefined' ) {
                window.location.reload();
            } else {
                window.location.href = lastSelectedComponent[0].dataset.link;
            }
        });

        // $(function () {
            $( '.customize__variations .variations__link' ).each( function() {
                let $this = $( this );
                $this.click();
            } );
            window.initToggleSwitch = new Event('initToggleSwitch' );
            document.dispatchEvent( window.initToggleSwitch );
            $( '.js-toggle-switch' ).on( 'change', toggleSwitch );
            isInit = true;
        // });
        // circleProgressBars.forEach(circleProgressBar => {
        //     circleProgressBar.destroy();
        // });
    } );

} ) ( jQuery );
