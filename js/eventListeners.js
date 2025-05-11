/** @description Replaces the modal element's 'hideModal' class with 'showModal' and vice versa
 * @param e - The element object which needs its classes swapped
 * @return void
 */
function changeModalDisplay(e) {
    e.classList.contains("hideModal") ? e.classList.replace("hideModal", "showModal") : e.classList.replace("showModal", "hideModal");
}

/** @description Copies the name of the delete/edit btn to the modal for use in submission processing
 *
 * @param i - The element object to transfer the name of the delete/edit button to
 * @return void
 */
function transferName(i) {
    // excludes trailing numbers
    let sub = i.id.substring(i.id.lastIndexOf('_') + 1, i.id.search(/\d/));
    let form = document.getElementById(sub + '-form');
    let el = document.createElement("input");
    el.setAttribute("type", "hidden");
    el.setAttribute("name", i.getAttribute("name"));
    form.append(el);
}

/** @description Switches the li tab element with the 'active' class and hides the other related cards (e.g. review-tab-recent has a related card with the id of recent-reviews)
 ** TODO - need to make more modular, as currently it only works for two tabs with specific ids
 *
 * @param i - The li element object to add the 'active' class to
 * @return void
 */
function changeActive(i) {
    const otherStr = ['all', 'recent'];
    // to make more modular, just swap the logic around: use the index of the li to add active to instead of the one not to add it to
    let otherIdx;
    i.id === 'review-tab-recent' ? otherIdx = 0 : otherIdx = 1;
    if (!i.classList.contains('active')) {
        i.classList.add('active');
        i.children.item(0).classList.add('active');

        // adds the hidden attribute to the opposite card and removes it from the related card
        // only works for two entries, will need to refactor if adding more tabs
        for (const [index, value] of otherStr.entries()) {
            document.getElementById(value + '-reviews').hidden = (otherIdx === index);
        }

        let otherli = document.getElementById('review-tab-' + otherStr[otherIdx]);
        otherli.classList.remove('active');
        otherli.children.item(0).classList.remove('active');
    }
}

function showOnLoad(e) {
    if (e.hidden) e.hidden = false;
    document.getElementById('spinner').hidden = true;
}