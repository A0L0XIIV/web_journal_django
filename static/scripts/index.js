function switchDarkTheme() {
  // Toggle class adds or removes the class depending on the  class's presence
  /* Swicht body theme*/
  $("body").toggleClass("dark-body");
  /* Swicht main div theme*/
  $("main").toggleClass("dark-main");
  /* Swicht navbar theme*/
  $(".navbar").toggleClass("navbar-light");
  $(".navbar").toggleClass("navbar-dark");
  $(".nav-item > a").toggleClass("dark-main");
  /* Swicht modal-content div theme*/
  $(".modal-content").toggleClass("dark-modal");
  $(".modal-content .close").toggleClass("text-white");
}