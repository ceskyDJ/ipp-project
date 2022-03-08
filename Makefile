.PHONY: pack clean

pack:
	cd src && tar -czvf ../xsmahe01.tgz parse/ *.php *.md rozsireni

clean:
	rm xsmahe01.tgz
