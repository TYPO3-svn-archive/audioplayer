var AudioPlayer=function(){
	var F=[];
	var C;
	var E="";
	var A={};
	var D=-1;
	function B(G){
		return document.all?window[G]:document[G]
	}
	
	return {
		setup:function(H,G){
			E=H;
			A=G
		},
		getPlayer:function(G){
			return B(G)
		},
		embed:function(K,O){
			var I={};
			var M;
			var G;
			var P;
			var H;
			var N={};
			var J={};
			var L={};
			for(M in A){
				I[M]=A[M]}
			for(M in O){
				I[M]=O[M]}
			if(I.transparentpagebg=="yes"){
				N.bgcolor="#FFFFFF";
				N.wmode="transparent"
			}else{
				if(I.pagebg){
					N.bgcolor="#"+I.pagebg
				}
				N.wmode="opaque"
			}
			N.menu="false";
			for(M in I){
				if(M=="pagebg"||M=="width"||M=="transparentpagebg"){
					continue
				}
				J[M]=I[M]
			}
			L.name=K;
			L.style="outline: none";
			J.playerID=K;
			audioplayer_swfobject.embedSWF(E,K,I.width.toString(),"24","9.0.0",false,J,N,L);
			F.push(K)
		},
		syncVolumes:function(G,I){
			D=I;
			for(var H=0;H<F.length;H++){
				if(F[H]!=G){
					B(F[H]).setVolume(D)
				}
			}
		},
		activate:function(G){
			if(C&&C!=G){
				B(C).close()
			}
			C=G
		},
		load:function(I,G,J,H){
			B(I).load(G,J,H)
		},
		close:function(G){
			B(G).close();
			if(G==C){
				C=null
			}
		},
		open:function(G){
			B(G).open()
		},
		getVolume:function(G){
			return D
		}
	}
}()