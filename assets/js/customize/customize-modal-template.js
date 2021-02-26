// current component item in loop
let current;
let firstComponent = true;
let otherComponentsScore = [];
let otherComponentCals;
// initial data for customize footer
export let checkedComponentData = {};
let currentCheckedElArrayKey;

const template = ( data ) => (
    `<div class="modal-builder modal-common" id="meals-variation-group-${ data.currentBlock }" style="display: none">
        <form class="modal-builder__form" action="#"
              method="post"
              data-product="${ data.productId }"
              data-variation="${ data.currentVariationInfo.var_id }"
              data-form="${ data.currentBlock }">
            <header class="modal-builder__header builder-header">
                ` +
                    recommendedBlock()
                + `
                <h3 class="builder-header__title ${ surveyExists ? '' : 'title-center' }">Select replacement</h3>
            </header>
            <div class="modal-builder__body builder-body js-perfect-scrollbar" style="overflow: scroll;">
                <ul class="builder-body__options option-list option-list-${ data.currentBlock }">
                ` +
                    templateComponents( data )
                + `
                </ul>
            </div>
            <footer class="modal-builder__footer builder-footer">
                <div class="builder-footer__left content js-perfect-scrollbar">
                    <h4 id="customizer-footer-title">${ checkedComponentData.name }</h4>
                    <p id="customizer-footer-description">${ checkedComponentData.descr }</p>
                </div>
                <div class="builder-footer__right">
                    <ul class="builder-footer__nutrition-list nutrition-list nutrition-list--small">
                        <li class="nutrition-list__item nutrition-item">
                            <div class="nutrition-item__progress-bar progress-bar progress-bar--small summ_cal"
                                 data-value-grams="${ checkedComponentData.cals }"
                                 data-value-percent="${ checkedComponentData.cals }"
                                 data-color="#fff">
                            </div>
                            <p class="nutrition-item__title">Cal</p>
                        </li>
                        <li class="nutrition-list__item nutrition-item">
                            <div class="nutrition-item__progress-bar progress-bar progress-bar--small summ_carbs"
                                 data-value-grams="${ checkedComponentData.carbs } g"
                                 data-value-percent="${ calculatePercentage( checkedComponentData.carbs, checkedComponentData.totalCals ) }"
                                 data-color="#0482CC">
                            </div>
                            <p class="nutrition-item__title">Carbs</p>
                        </li>
                        <li class="nutrition-list__item nutrition-item">
                            <div class="nutrition-item__progress-bar progress-bar progress-bar--small summ_fat"
                                 data-value-grams="${ checkedComponentData.fats } g"
                                 data-value-percent="${ calculatePercentage( checkedComponentData.fats, checkedComponentData.totalCals ) }"
                                 data-color="#F2AE04">
                            </div>
                            <p class="nutrition-item__title">Fat</p>
                        </li>
                        <li class="nutrition-list__item nutrition-item">
                            <div class="nutrition-item__progress-bar progress-bar progress-bar--small summ_protein"
                                 data-value-grams="${ checkedComponentData.protein } g"
                                 data-value-percent="${ calculatePercentage( checkedComponentData.protein, checkedComponentData.totalCals ) }"
                                 data-color="#34A34F">
                            </div>
                            <p class="nutrition-item__title">Protein</p>
                        </li>
                    </ul>
                    <button class="builder-footer__button button" type="button">
                        Apply $
                        <span id="footer-price"> ${ data.startVariationPrice }</span>
                    </button>
                </div>
            </footer>
        </form>
    </div>
    `);

const recommendedBlock = function () {
    if ( is_user_logged_in && surveyExists ) {
        return `<div class="builder-header__toggle toggle toggle--easy">
                <!--  id="products-filter-disable-survey" -->
                <input class="js-toggle-switch visually-hidden" type="checkbox" name="use_survey" 
                    ${ surveyExists ? 'checked' : '' }/>
                <span class="toggle__txt">Show recommended only</span>
            </div>`
    }
    return '';
}

const templateComponents = function ( data ) {
    let componentsStr = [];
    let finalStr = '';
    otherComponentCals = calculateScore(data.currentVariationInfo.var_attributes, data.componentData)

    let i, j;
    if ( data.currentBlock == 'pa_part-1' ) {
        i = 1;
        j = 2;
    } else if ( data.currentBlock == 'pa_part-2' ) {
        i = 0;
        j = 2;
    } else {
        i = 0;
        j = 1;
    }
    for ( let key in data.componentData ) {
        current = data.componentData[ key ];
        const carbs = current._op_variations_component_carbohydrates;
        const fats = current._op_variations_component_fats;
        const protein = current._op_variations_component_proteins;
        const totalCals = 2500;
        componentsStr[ current._op_variations_component_sku ] =
        `<li class="option-list__item">
            <label class="option-item"
               data-component-id="${ current._op_variations_component_sku }"
               data-link="${ data.currentVariationInfo.var_link }"
               data-name="${ current.name }"
               data-desc="${ current.op_variations_component_description }"
               data-price="${ current._price }"
               data-cal="${ Number( current._op_variations_component_calories ) + otherComponentCals[i][0] + otherComponentCals[j][0] }"
               data-carbs="${ Number( carbs ) + otherComponentCals[i][2] + otherComponentCals[j][2] }"
               data-fat="${ Number( fats ) + otherComponentCals[i][3] + otherComponentCals[j][3] }"
               data-protein="${ Number( protein ) + otherComponentCals[i][1] + otherComponentCals[j][1] }"
               data-carbs-percentage="${ calculatePercentage( Number( carbs ) + otherComponentCals[i][2] + otherComponentCals[j][2], totalCals ) }"
               data-fat-percentage="${ calculatePercentage( Number( fats ) + otherComponentCals[i][3] + otherComponentCals[j][3], totalCals ) }"
               data-protein-percentage="${ calculatePercentage( Number( protein ) + otherComponentCals[i][1] + otherComponentCals[j][1], totalCals ) }">
                <input class="option-item__field visually-hidden"
                       type="radio"
                       name="moroccan-chicken-stew"
                       ${ isComponentInMeal( current, data.currentVariationInfo.var_attributes, data.currentBlock, i, j ) }
                       >
                <span class="option-item__box">
                    <span class="option-item__img-box">
                        <picture>
                            ${ current._op_variations_component_thumb }
                        </picture>
                    </span>
                     <span class="option-item__info">
                        <span class="option-item__name">
                            ${ current.name }
                        </span>
                        ` +
                            chefHatsCalculate( data.currentVariationInfo.var_attributes, current, data.componentData, data.currentBlock )
                        + `
                    </span>
                </span>
            </label>
        </li>`;
    }

    finalStr += componentsStr[ currentCheckedElArrayKey ];
    componentsStr.splice( currentCheckedElArrayKey, 1 );
    for ( let elem of componentsStr ) {
        if ( typeof elem !== 'undefined' ) {
            finalStr+= elem;
        }
    }

    return finalStr
};

export const handleOpen = ( customizeItemData ) => {
    const body = jQuery( 'body' );
    if ( ! jQuery( '#meals-variation-group-' + customizeItemData.currentBlock ).length ) {
        body.append( template( customizeItemData ) );
    } else {
        jQuery( '#meals-variation-group-' + customizeItemData.currentBlock + ' ul.option-list')
            .html( templateComponents( customizeItemData ) )

        if ( surveyExists ) {
            const event = new Event('init_ajax_rating');
            document.dispatchEvent( event );
        }
    }
}

const calculatePercentage = function ( item, total ) {
    if ( total > 0 ) {
        return Math.round( item * 100 / total )
    }

    return 0
}

const chefHatsCalculate = function ( mealComponentsData, currentLoopComponent, allComponentsData, block ) {
    let score = 2;

    if ( surveyExists ) {
        if ( firstComponent ) {
            block = block.replace( /[a-zA-Z-_]*/g, '' );
            for ( let i = 0; i < mealComponentsData.length; i++ ) {

                if ( i == block - 1 ) continue;
                for ( let el in allComponentsData  ) {
                    if ( allComponentsData[ el ].slug === mealComponentsData[ i ].slug ) {
                        otherComponentsScore.push( allComponentsData[ el ].chef_score )
                    }
                }
            }
            firstComponent = false;
        }

        score += currentLoopComponent.chef_score;
        score += otherComponentsScore[0];
        score += otherComponentsScore[1];


        if ( score >= 5 ) {
            return `<span class="option-item__label label-best">Best for you</span>`
        } else {
            return `<div class="option-item__rating rating-extra js-rating--readonly--true" data-rate-value="${ score }"></div>`
        }
    }

    return ''
}

const calculateScore = function ( mealComponentsData, allComponentsData ) {
    let otherComponentCals = [];
    for ( let i = 0; i < mealComponentsData.length; i++ ) {
        for ( let el in allComponentsData  ) {
            if ( allComponentsData[ el ].slug === mealComponentsData[ i ].slug ) {
                otherComponentCals.push( [
                    Number( allComponentsData[ el ]._op_variations_component_calories ),
                    Number( allComponentsData[ el ]._op_variations_component_proteins ),
                    Number( allComponentsData[ el ]._op_variations_component_carbohydrates ),
                    Number( allComponentsData[ el ]._op_variations_component_fats )
                ] );
            }
        }
    }

    return otherComponentCals
}

const isComponentInMeal = function( component, mealComponents, numberOfBlock, i, j ) {
    numberOfBlock = numberOfBlock.replace( /[a-zA-Z-_]*/g, '' );
    if ( component.slug == mealComponents[ numberOfBlock - 1 ].slug ) {
        currentCheckedElArrayKey = current._op_variations_component_sku
        checkedComponentData = {
            name: current.name,
            slug: current.slug,
            descr: current.op_variations_component_description,
            cals: Number( current._op_variations_component_calories ) + otherComponentCals[i][0] + otherComponentCals[j][0],
            carbs: Number( current._op_variations_component_carbohydrates ) + otherComponentCals[i][2] + otherComponentCals[j][2],
            fats: Number( current._op_variations_component_fats ) + otherComponentCals[i][3] + otherComponentCals[j][3],
            protein: Number( current._op_variations_component_proteins ) + otherComponentCals[i][1] + otherComponentCals[j][1],
            totalCals: 2500,
            price: Number( current._price ).toFixed( 2 ),
            netsuit_id: currentCheckedElArrayKey
        }
        return 'checked'
    }

    return ''
}
