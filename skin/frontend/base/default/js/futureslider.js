/**
 * Fallback for simple animations
 * 
 * No need to try requestAnimationFrame or fancier, modern browsers will use CSS anim instead
 */

self.AnimationEvent || self.OAnimationEvent || self.MozAnimationEvent || self.webkitAnimationEvent || 
(Futureslider = {
	playKeyframes: function (animation) {
		animation = $H(animation);
		var times = animation.keys();
		times.sort(function(a,b){return a-b});

		// start a frame
		function play(frame) {
			frame|=0;
			var start = times[frame],
				curr = animation.get(start),
				nextFrame = ++frame < times.length ? frame : 0,
				end = times[nextFrame],
				next = animation.get(end),
				effects = [];

			// enforce non-animatable styles
			// this helps Effect.Morph calculate the "from"
			for (var id in curr) {
				Element.setStyle(id, curr[id]);
			}

			// attempt animating here
			for (var id in next) {
				var timing = curr[id] && curr[id].animationTimingFunction,
					transition = Effect.Transitions[timing] ? Effect.Transitions[timing] : Effect.Transitions.sinoidal;
				effects.push(new Effect.Morph(id, {
					style: next[id],
					sync: true,
					transition: transition
				}));
			}
			new Effect.Parallel(effects, {
				duration: end - start,
				afterFinish: play.curry(nextFrame)
			});
		}

		// start immediately if already loaded, or wait
		(document.loaded && play()) || document.observe('dom:loaded', play);
	}
});

Effect.Transitions['step-start'] = Effect.Transitions.full;
Effect.Transitions['step-end'] = Effect.Transitions.none;

self.SVGSVGElement || document.observe('dom:loaded', function(){
	// hope the size doesn't change too much after load
	$$('.future-image').each(function(object){
		var dims = Element.getDimensions(object),
			src;
		Element.readAttribute(object, 'data-sizes').split(';').eachSlice(3, function(size){
			var url = size[0],
				width = size[1],
				height = size[2];
			if ((width >= dims.width && height >= dims.height) || (!src)) {
				src = url;
			}
		});
		Element.insert(object, '<img class="raster-fallback" src="'+src+'"/>');
	});
});
