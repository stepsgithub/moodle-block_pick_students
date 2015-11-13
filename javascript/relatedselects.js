    $(document).ready(function() {
            $("#students").relatedSelects({
                onChangeLoad: "eip.php",
                defaultOptionText: strchoose,
                disableIfEmpty: true,
                dataType: "html",
            selects: {
                "_sid":       { loadingMessage: "loading..." },
                "_ban":       { onChange: function() {
                    $("#students").attr("action", "");
                    $("#students").submit();
                } }
            }
            });
            $("#students").relatedSelects({
                onChangeLoad: "course.php",
                defaultOptionText: strchoose,
                disableIfEmpty: true,
                dataType: "html",
                onLoadingEnd: function() { $("#approve").attr("disabled", "disabled").button("disable") },
                selects: {
                    "category":       { loadingMessage: "loading..." },
                    "assign_course":  { loadingMessage: "loading..." },
                    "assign_roleid":  { onChange: function() { $("#approve").removeAttr("disabled").button("enable") } }
                }
            });
            $("#students").relatedSelects({
                selects: {
                    "assign_roleid_course":  { onChange: function() {
                        if ($(this).val() > 0)
                            $("#approve").removeAttr("disabled").button("enable");
                        else
                            $("#approve").attr("disabled", "disabled").button("disable"); } }
                }
            });
    });
