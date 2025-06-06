/** @description Replaces the modal element's 'hideModal' class with 'showModal' and vice versa
 * @param e - The element object which needs its classes swapped
 * @return void
 */
function changeModalDisplay(e) {
    e.classList.contains("hideModal") ? e.classList.replace("hideModal", "showModal") : e.classList.replace("showModal", "hideModal");
}

/** @description Copies the name of the delete/edit btn to the modal for use in submission processing
 *
 * @param i - The element object that needs its name transferred to the appropriate form
 * @param type - Either delete or edit, defaults to delete
 * @return void
 */
function transferName(i, type = "delete") {
    let sub;
    if (type === "edit") {
        sub = 'review';
    } else {
        sub = 'alert';
    }
    // excludes trailing numbers
    //let sub = i.id.substring(i.id.lastIndexOf('_') + 1, i.id.search(/\d/));
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
 * @param extraArr - Optional, an array of any ancillary elements to hide/show
 * @return void
 */
function changeActive(i, iHref, otherElement, otherHref, extraArr = []) {
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

/** Hides the spinner element and shows the loaded content
 * @param e - The element to show
 */
function showOnLoad(e) {
    if (e.hidden) e.hidden = false;
    document.getElementById('spinner').hidden = true;
}

/** Fills the review modal with the content of the review to be edited
 * @param inputEl - The textarea element to fill with textual content
 * @param ratingEl - The radio button star element to have checked
 * @param content - The textual content to fill inputEl with
 */
function fillEditModal(inputEl, ratingEl, content = "") {
    inputEl.innerText = content;
    ratingEl.setAttribute("checked", true);
}
