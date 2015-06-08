/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Daniel Deady
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

document.observe('dom:loaded', function(){
	Effect.Transitions['step-start'] = Effect.Transitions.full;
	Effect.Transitions['step-end'] = Effect.Transitions.none;

	// Fallback for simple animations
	// No need to try requestAnimationFrame or fancier, modern browsers will use CSS anim instead
	self.AnimationEvent || self.OAnimationEvent || self.MozAnimationEvent || self.webkitAnimationEvent ||
	$$('.widget-future-banner[data-keyframes]').each(function(banner){
		var keyframes = $(banner).getAttribute('data-keyframes').evalJSON(true),
			animation = $H(keyframes),
			times = animation.keys();
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
		play();
	});

	// Responsive image without SVG
	// hope the banner doesn't change size too much after load
	self.SVGSVGElement || $$('.future-image').each(function(object){
		var actual = Element.getDimensions(object),
			src, aspect, style='', targetSize=Element.readAttribute(object, 'data-size');
		actual.aspect = actual.height / actual.width;
		Element.readAttribute(object, 'data-sizes').split(';').eachSlice(3, function(size){
			var url = size[0],
				width = size[1],
				height = size[2];
			if ((width >= actual.width && height >= actual.height) || (!src)) {
				src = url;
				aspect = height / width;
			}
		});
		if (targetSize == 'cover') targetSize = aspect < actual.aspect ? 'auto 100%' : '100% auto';
		else if (targetSize == 'contain') targetSize = aspect < actual.aspect ? '100% auto' : 'auto 100%';
		if (targetSize == '100% auto') style = 'width: 100%; height: auto; margin: '+((actual.height-actual.width*aspect)/2)+'px 0;';
		else if (targetSize == 'auto 100%') style = 'width: auto; height: 100%; margin: 0 '+((actual.width-actual.height/aspect)/2)+'px;';
		else style = 'width: auto; height: auto;';
		Element.update(object, '<img class="raster-fallback" src="'+src+'" style="'+style+'" />');
	});
});

