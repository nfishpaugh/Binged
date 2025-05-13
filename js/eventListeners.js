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
 *
 * @param i - The li element object to add the 'active' class to
 * @param iHref - The element that i displays when clicked
 * @param otherElement - The other li in a group of two nav tabs to remove the 'active' class from
 * @param otherHref - The element that otherElement displays when clicked
 * @param extraArr - Optional, an array of any ancilliary elements to hide/show
 * @return void
 */
function changeActive(i, iHref, otherElement, otherHref, extraArr = []) {
    // to make more modular, just swap the logic around: use the index of the li to add active to instead of the one not to add it to
    if (!i.classList.contains('active')) {
        i.classList.add('active');
        i.children.item(0).classList.add('active');
        if (iHref.hidden) iHref.hidden = false;

        if (otherElement.classList.contains("active")) otherElement.classList.remove("active");
        if (otherElement.children.item(0).classList.contains("active")) otherElement.children.item(0).classList.remove('active');
        if (!otherHref.hidden) otherHref.hidden = true;
    }

    if (extraArr.length !== 0) {
        for (let x of extraArr) {
            x.hidden = !x.hidden;
        }
    }
}

function showOnLoad(e) {
    if (e.hidden) e.hidden = false;
    document.getElementById('spinner').hidden = true;
}