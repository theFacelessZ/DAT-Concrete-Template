var jDebug = {
    enable: true,
    startTime: new Date(),
    spanTimeSec: function() {
        return ((new Date()) - this.startTime) / 1000;
    },
    log: function(string) {
        if (this.enable) {
            console.log('[' + this.spanTimeSec() + '] ' + string);
        }
    }
};

$(document).ready(function() {
    console.log("         /\                                              .-."); console.log("     _  / |             /   /                           / (_)");
    console.log("    (  /  |  ..  .-.---/---/-.  .-._..  .-.    .    .-./      )  (  .-.  .-._.`)    ("); console.log("     `/.__|_.' )/   ) /   /   |(   )  )/   )    )  /  /      (    ) /  )(   ) /  .   )");
    console.log(" .:' /    |   '/   ( / _.'    | `-'  '/   (    (_.'.-/.    .-.`--':/`-'  `-' (_.' `-'"); console.log("(__.'     `-'       `-                     `-..-._)(_/ `-._.       /");
    console.log("┌─────────────────────────┐"); console.log("│ anthony.lupow@gmail.com │"); console.log("└─────────────────────────┘");
});