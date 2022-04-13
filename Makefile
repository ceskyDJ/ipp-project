.PHONY: pack clean

pack:
	cd src && tar -czvf ../xsmahe01.tgz parse/ test/ interpreter/*.py *.php *.py *.md rozsireni

clean:
	rm xsmahe01.tgz
