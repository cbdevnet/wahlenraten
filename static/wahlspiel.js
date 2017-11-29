var wahlspiel = {
	//TODO optionally, allow a sink assignment for primary balancing
	lastModified:null,

	reset:function(){
		for(var i = 0; i < wahlspiel.options.length; i++){
			wahlspiel.options[i].style.backgroundColor = "";
			var slider = wahlspiel.options[i].getElementsByClassName("slider")[0];
			slider.value = slider.getAttribute("data-default");
			slider.removeAttribute("data-set");
		}
		wahlspiel.balance();
		wahlspiel.resetLink.style.display = "none";
	},

	init:function(){
		//workaround
		wahlspiel.options = document.getElementsByClassName("option");
		wahlspiel.resetLink = document.getElementById("reset");
	
		//hook all handlers
		for(var i = 0; i < wahlspiel.options.length; i++){
			wahlspiel.options[i].setAttribute("data-index", i);
			var slider = wahlspiel.options[i].getElementsByClassName("slider")[0];
			slider.oninput = wahlspiel.inputHandler;
			slider.onchange = wahlspiel.changeHandler;
			//set the initial reset value if not provided
			if(!slider.getAttribute("data-initial")){
				slider.setAttribute("data-initial", slider.value);
			}
		}


		//balance all options and update displays
		wahlspiel.balance();
	},

	updateDisplay:function(e){
		var slider = e.getElementsByClassName("slider")[0];
		var display = e.getElementsByClassName("option-value")[0];
		display.textContent = Math.round((parseFloat(slider.value) / parseFloat(slider.max)) * 10000) / 100 + "%";
	},

	balance:function(){
		var max = 0;
		var assigned = 0;
		var weight = 0;

		//get remaining unassigned weight
		for(i = 0; i < wahlspiel.options.length; i++){
			var slider = wahlspiel.options[i].getElementsByClassName("slider")[0];
			max = slider.max;
			if(slider.hasAttribute("data-set")){
				assigned += parseFloat(slider.getAttribute("data-set"));
			}
			else{
				weight += parseFloat(slider.getAttribute("data-initial")) / parseFloat(slider.max);
			}
		}

		if(assigned > max){
			//mark sliders exceeding the maximum
			wahlspiel.lastModified.style.backgroundColor = "#f66";
		}
		else{
			//update positions of unset sliders
			for(i = 0; i < wahlspiel.options.length; i++){
				var slider = wahlspiel.options[i].getElementsByClassName("slider")[0];
				if(!slider.hasAttribute("data-set")){
					slider.value = (max - assigned) * (parseFloat(slider.getAttribute("data-initial")) / parseFloat(slider.max) / weight)
				}
				else{
					wahlspiel.options[i].style.backgroundColor = "#6f6";
				}
			}
		}

		//update displays
		for(var i = 0; i < wahlspiel.options.length; i++){
			wahlspiel.updateDisplay(wahlspiel.options[i]);
		}
	},

	changeHandler:function(e){
		wahlspiel.lastModified = e.target.parentElement;

		//mark as set
		e.target.setAttribute("data-set", e.target.value);
		wahlspiel.resetLink.style.display = "block";

		//update equilibrium
		wahlspiel.balance();
	},

	inputHandler:function(e){
		wahlspiel.lastModified = e.target.parentElement;
		wahlspiel.updateDisplay(wahlspiel.lastModified);
	}
};
